<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        protected MercadoPagoService $mercadoPagoService
    ) {}

    /**
     * Handle incoming webhook notifications from Mercado Pago.
     */
    public function handle(Request $request): JsonResponse
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        $dataId = $request->input('data.id') ?? $request->input('id') ?? $request->query('data_id') ?? $request->query('id');
        $type = $request->input('type') ?? $request->query('type') ?? $request->input('topic') ?? $request->query('topic');

        Log::info('MercadoPago webhook notification received', [
            'type' => $type,
            'data_id' => $dataId,
        ]);

        if (empty($dataId)) {
            return response()->json(['message' => 'Resource ID missing'], 200);
        }

        // Validação da assinatura criptográfica x-signature
        if (!$this->mercadoPagoService->validateSignature($xSignature, $xRequestId, (string) $dataId)) {
            Log::warning('MercadoPago webhook invalid signature', [
                'x-signature' => $xSignature,
                'data_id' => $dataId,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        if ($type === 'payment') {
            $this->processPaymentNotification((string) $dataId);
        } elseif ($type === 'subscription_preapproval' || $type === 'preapproval') {
            $this->processPreapprovalNotification((string) $dataId);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Process payment event and reconcile subscription and company state.
     */
    protected function processPaymentNotification(string $paymentId): void
    {
        $paymentData = $this->mercadoPagoService->getPayment($paymentId);

        if (!$paymentData) {
            Log::warning("Payment data not found for payment ID {$paymentId}");
            return;
        }

        $status = $paymentData['status'] ?? 'pending';
        $amount = (float) ($paymentData['transaction_amount'] ?? 0);
        $paymentMethod = $paymentData['payment_method_id'] ?? 'unknown';
        $externalReference = $paymentData['external_reference'] ?? '';

        // Extrai company_id e plan do external_reference (ex: company_1_plan_pro)
        $companyId = null;
        $plan = 'starter';

        if (preg_match('/company_(\d+)_plan_([a-z0-9_]+)/', $externalReference, $matches)) {
            $companyId = (int) $matches[1];
            $plan = $matches[2];
        }

        $company = $companyId ? Company::find($companyId) : null;

        if (!$company) {
            // Tenta encontrar fatura já gravada previamente
            $existingInvoice = Invoice::where('mp_payment_id', $paymentId)->first();
            if ($existingInvoice) {
                $company = $existingInvoice->company;
            }
        }

        if (!$company) {
            Log::warning("Company not found for payment {$paymentId}", ['external_reference' => $externalReference]);
            return;
        }

        // Idempotência e registro da fatura
        $invoice = Invoice::firstOrNew(['mp_payment_id' => $paymentId]);
        $invoice->company_id = $company->id;
        $invoice->amount = $amount > 0 ? $amount : $this->mercadoPagoService->getPlanDetails($plan)['price'];
        $invoice->payment_method = $paymentMethod;
        $invoice->status = match ($status) {
            'approved' => 'paid',
            'pending', 'in_process', 'authorized' => 'pending',
            'refunded', 'charged_back' => 'refunded',
            default => 'failed',
        };

        if ($status === 'approved') {
            $invoice->paid_at = now();
        }

        $invoice->save();

        // Processa ativação de plano em caso de pagamento aprovado (D-01, D-04)
        if ($status === 'approved') {
            $planDetails = $this->mercadoPagoService->getPlanDetails($plan);

            // Cria ou atualiza a assinatura ativa para novo ciclo de 30 dias (D-04)
            $subscription = Subscription::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'status' => 'active',
                ],
                [
                    'plan' => $plan,
                    'amount' => $invoice->amount,
                    'billing_cycle' => 'monthly',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(30),
                    'grace_period_ends_at' => null,
                ]
            );

            $invoice->subscription_id = $subscription->id;
            $invoice->save();

            // Atualiza status e cota de usuários da empresa
            $company->update([
                'status' => 'active',
                'plan' => $plan,
                'max_users' => $planDetails['max_users'],
            ]);

            Log::info("Company {$company->id} activated with plan {$plan} and {$planDetails['max_users']} max users");
        } elseif (in_array($status, ['rejected', 'cancelled'])) {
            // Inicia janela de carência de 5 dias corridos (D-03)
            if ($company->isActive()) {
                $company->update(['status' => 'overdue']);

                $activeSubscription = $company->activeSubscription();
                if ($activeSubscription) {
                    $activeSubscription->update([
                        'status' => 'overdue',
                        'grace_period_ends_at' => now()->addDays(5),
                    ]);
                }

                Log::info("Company {$company->id} set to overdue with 5 days grace period");
            }
        }
    }

    /**
     * Process recurring subscription preapproval notification.
     */
    protected function processPreapprovalNotification(string $preapprovalId): void
    {
        $preapprovalData = $this->mercadoPagoService->getPreapproval($preapprovalId);

        if (!$preapprovalData) {
            return;
        }

        $status = $preapprovalData['status'] ?? 'pending';
        $externalReference = $preapprovalData['external_reference'] ?? '';

        if (preg_match('/company_(\d+)_plan_([a-z0-9_]+)/', $externalReference, $matches)) {
            $companyId = (int) $matches[1];
            $plan = $matches[2];
            $company = Company::find($companyId);

            if ($company) {
                $planDetails = $this->mercadoPagoService->getPlanDetails($plan);

                if ($status === 'authorized') {
                    Subscription::updateOrCreate(
                        ['company_id' => $company->id, 'mp_preapproval_id' => $preapprovalId],
                        [
                            'plan' => $plan,
                            'status' => 'active',
                            'amount' => $planDetails['price'],
                            'current_period_start' => now(),
                            'current_period_end' => now()->addDays(30),
                            'grace_period_ends_at' => null,
                        ]
                    );

                    $company->update([
                        'status' => 'active',
                        'plan' => $plan,
                        'max_users' => $planDetails['max_users'],
                    ]);
                } elseif (in_array($status, ['paused', 'cancelled'])) {
                    $company->update(['status' => 'overdue']);
                }
            }
        }
    }
}
