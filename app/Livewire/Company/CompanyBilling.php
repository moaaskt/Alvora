<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyBilling extends Component
{
    use WithPagination;

    public bool $showCancelModal = false;
    public string $cancelReason = '';
    public string $errorMessage = '';

    /**
     * Mount lifecycle hook.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->isCompanyAdmin() && !$user->isAlvoraAdmin()) {
            abort(403, 'Acesso restrito ao gestor de RH da empresa.');
        }
    }

    /**
     * Start subscription checkout on Mercado Pago for upgrade or plan change (D-01, D-04).
     */
    public function startCheckout(string $plan, ?MercadoPagoService $service = null)
    {
        $service = $service ?? app(MercadoPagoService::class);
        $this->errorMessage = '';
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            $this->errorMessage = 'Nenhuma empresa associada ao seu usuário.';
            return;
        }

        try {
            $preference = $service->createCheckoutPreference($company, $plan, $user);

            // Redireciona para o checkout do Mercado Pago
            $checkoutUrl = $preference['init_point'] ?? $preference['sandbox_init_point'] ?? null;

            if ($checkoutUrl) {
                return redirect()->away($checkoutUrl);
            }

            $this->errorMessage = 'Não foi possível gerar o link de pagamento do Mercado Pago.';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Falha ao iniciar checkout: ' . $e->getMessage();
        }
    }

    /**
     * Open confirmation modal for cancellation.
     */
    public function openCancelModal(): void
    {
        $this->showCancelModal = true;
    }

    /**
     * Close confirmation modal.
     */
    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelReason = '';
    }

    /**
     * Cancel the active subscription.
     */
    public function cancelSubscription(): void
    {
        $company = Auth::user()->company;

        if ($company) {
            $subscription = $company->activeSubscription();
            if ($subscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
            }

            $company->update(['status' => 'suspended']);

            session()->flash('status', 'Assinatura cancelada com sucesso. O acesso aos cursos foi suspenso.');
        }

        $this->closeCancelModal();
    }

    public function render()
    {
        $company = Auth::user()->company;

        $activeSubscription = $company ? $company->activeSubscription() : null;

        $invoices = $company
            ? Invoice::where('company_id', $company->id)->latest('id')->paginate(10)
            : null;

        $currentUsersCount = $company ? $company->users()->count() : 0;
        $maxUsers = $company ? $company->max_users : 20;
        $slotPercentage = $maxUsers > 0 ? min(100, round(($currentUsersCount / $maxUsers) * 100)) : 0;

        return view('livewire.company.company-billing', [
            'company' => $company,
            'activeSubscription' => $activeSubscription,
            'invoices' => $invoices,
            'currentUsersCount' => $currentUsersCount,
            'maxUsers' => $maxUsers,
            'slotPercentage' => $slotPercentage,
        ])->layout('layouts.app');
    }
}
