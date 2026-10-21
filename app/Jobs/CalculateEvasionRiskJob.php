<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use App\Services\EvasionRiskCalculatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateEvasionRiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $companyId = null,
        public ?int $userId = null
    ) {}

    public function handle(EvasionRiskCalculatorService $calculator): void
    {
        Log::info('Iniciando cálculo de risco de evasão...', [
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
        ]);

        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user && $user->isStudent()) {
                $calculator->calculateForUser($user);
            }
            return;
        }

        if ($this->companyId) {
            $company = Company::find($this->companyId);
            if ($company) {
                $calculator->calculateForCompany($company);
            }
            return;
        }

        // Processa todos os alunos da plataforma em lotes
        User::where('role', 'student')->chunk(100, function ($students) use ($calculator) {
            foreach ($students as $student) {
                $calculator->calculateForUser($student);
            }
        });

        Log::info('Cálculo de risco de evasão finalizado.');
    }
}
