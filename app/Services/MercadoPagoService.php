<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected string $accessToken;
    protected string $publicKey;
    protected string $webhookSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token', '');
        $this->publicKey = config('services.mercadopago.public_key', '');
        $this->webhookSecret = config('services.mercadopago.webhook_secret', '');
        $this->baseUrl = rtrim(config('services.mercadopago.base_url', 'https://api.mercadopago.com'), '/');
    }

    /**
     * Get plan configuration (price, name, max users).
     */
    public function getPlanDetails(string $plan): array
    {
        return match (strtolower($plan)) {
            'pro' => [
                'name' => 'Pro + IA',
                'price' => 1290.00,
                'max_users' => 100,
                'description' => 'Acesso completo com IA preditiva de evasão para até 100 colaboradores.',
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 2990.00,
                'max_users' => 500,
                'description' => 'Solução corporativa personalizada para grandes organizações.',
            ],
            default => [
                'name' => 'Starter',
                'price' => 490.00,
                'max_users' => 20,
                'description' => 'Capacitação enxuta para equipes de até 20 colaboradores.',
            ],
        };
    }

    /**
     * Create a Checkout Pro Preference on Mercado Pago.
     */
    public function createCheckoutPreference(Company $company, string $plan, User $payer): array
    {
        $details = $this->getPlanDetails($plan);

        $payload = [
            'items' => [
                [
                    'id' => "plan_{$plan}",
                    'title' => "Assinatura {$details['name']} — ALVORA B2B",
                    'description' => $details['description'],
                    'quantity' => 1,
                    'currency_id' => 'BRL',
                    'unit_price' => (float) $details['price'],
                ],
            ],
            'payer' => [
                'name' => $payer->name,
                'email' => $payer->email,
            ],
            'back_urls' => [
                'success' => url('/instrutor/faturamento/sucesso'),
                'pending' => url('/instrutor/faturamento/pendente'),
                'failure' => url('/instrutor/faturamento/erro'),
            ],
            'auto_return' => 'approved',
            'external_reference' => "company_{$company->id}_plan_{$plan}",
            'notification_url' => url('/api/webhooks/mercadopago'),
        ];

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/checkout/preferences", $payload);

        if (!$response->successful()) {
            Log::error('MercadoPago preference creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('Falha ao comunicar com o Mercado Pago: ' . ($response->json('message') ?? 'Erro inesperado.'));
        }

        return $response->json();
    }

    /**
     * Create a recurring subscription (Preapproval) on Mercado Pago.
     */
    public function createPreapproval(Company $company, string $plan, User $payer, string $cardTokenId): array
    {
        $details = $this->getPlanDetails($plan);

        $payload = [
            'reason' => "Assinatura Recorrente {$details['name']} — ALVORA B2B",
            'auto_recurring' => [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => (float) $details['price'],
                'currency_id' => 'BRL',
            ],
            'payer_email' => $payer->email,
            'card_token_id' => $cardTokenId,
            'back_url' => url('/instrutor/faturamento/sucesso'),
            'status' => 'authorized',
            'external_reference' => "company_{$company->id}_plan_{$plan}",
        ];

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/preapproval", $payload);

        if (!$response->successful()) {
            Log::error('MercadoPago preapproval creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('Falha ao criar assinatura no Mercado Pago: ' . ($response->json('message') ?? 'Erro inesperado.'));
        }

        return $response->json();
    }

    /**
     * Get payment details from Mercado Pago API.
     */
    public function getPayment(string $paymentId): ?array
    {
        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->get("{$this->baseUrl}/v1/payments/{$paymentId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::warning('MercadoPago getPayment failed', [
            'payment_id' => $paymentId,
            'status' => $response->status(),
        ]);

        return null;
    }

    /**
     * Get preapproval subscription details from Mercado Pago.
     */
    public function getPreapproval(string $preapprovalId): ?array
    {
        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->get("{$this->baseUrl}/preapproval/{$preapprovalId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Validate HMAC-SHA256 signature from x-signature header.
     * Format: ts=1710000000,v1=hash
     */
    public function validateSignature(?string $xSignature, ?string $xRequestId, ?string $dataId): bool
    {
        if (empty($this->webhookSecret)) {
            // Em ambiente local/testes sem secret configurado
            return true;
        }

        if (empty($xSignature) || empty($dataId)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $xSignature) as $item) {
            $kv = explode('=', trim($item), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        if (!isset($parts['ts']) || !isset($parts['v1'])) {
            return false;
        }

        $ts = $parts['ts'];
        $v1 = $parts['v1'];
        $requestId = $xRequestId ?? '';

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $calculatedHash = hash_hmac('sha256', $manifest, $this->webhookSecret);

        return hash_equals($calculatedHash, $v1);
    }
}
