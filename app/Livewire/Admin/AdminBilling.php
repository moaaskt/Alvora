<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Subscription;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdminBilling extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    /**
     * Mount lifecycle hook.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if (!$user || !$user->isAlvoraAdmin()) {
            abort(403, 'Acesso restrito ao Super Administrador da Alvora.');
        }
    }

    /**
     * Reset pagination when searching.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when changing filter.
     */
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Synchronize subscriptions with Mercado Pago API.
     */
    public function syncSubscriptions(?MercadoPagoService $service = null): void
    {
        $service = $service ?? app(MercadoPagoService::class);

        $user = Auth::user();
        if (!$user || !$user->isAlvoraAdmin()) {
            abort(403);
        }

        try {
            $subscriptions = Subscription::whereNotNull('mp_preapproval_id')->get();
            $syncedCount = 0;

            foreach ($subscriptions as $subscription) {
                $details = $service->getPreapproval($subscription->mp_preapproval_id);
                if ($details && isset($details['status'])) {
                    $mpStatus = match ($details['status']) {
                        'authorized' => 'active',
                        'paused' => 'overdue',
                        'cancelled' => 'cancelled',
                        default => $subscription->status,
                    };

                    $subscription->update(['status' => $mpStatus]);
                    $syncedCount++;
                }
            }

            session()->flash('status', "Sincronização concluída com sucesso. {$syncedCount} assinaturas verificadas com o Mercado Pago.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Falha ao sincronizar com o Mercado Pago: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // 1. KPIs
        $mrr = (float) Subscription::where('status', 'active')->sum('amount');
        $activeCompaniesCount = Company::where('status', 'active')->count();
        $overdueCompaniesCount = Company::where('status', 'overdue')->count();
        $suspendedCompaniesCount = Company::where('status', 'suspended')->count();

        // Breakdown por plano
        $starterCount = Company::where('status', 'active')->where('plan', 'starter')->count();
        $proCount = Company::where('status', 'active')->where('plan', 'pro')->count();
        $enterpriseCount = Company::where('status', 'active')->where('plan', 'enterprise')->count();

        // 2. Query de empresas para auditoria
        $query = Company::query()
            ->with([
                'subscriptions' => fn($q) => $q->latest('id'),
                'invoices' => fn($q) => $q->latest('id'),
            ]);

        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('cnpj', 'like', $term);
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $companies = $query->latest('id')->paginate(12);

        return view('livewire.admin.admin-billing', [
            'mrr' => $mrr,
            'activeCompaniesCount' => $activeCompaniesCount,
            'overdueCompaniesCount' => $overdueCompaniesCount,
            'suspendedCompaniesCount' => $suspendedCompaniesCount,
            'starterCount' => $starterCount,
            'proCount' => $proCount,
            'enterpriseCount' => $enterpriseCount,
            'companies' => $companies,
        ])->layout('layouts.admin', [
            'breadcrumb' => 'Faturamento B2B',
        ]);
    }
}
