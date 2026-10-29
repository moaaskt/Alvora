<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\TrackEnrollment;
use Illuminate\Console\Command;

class BackfillTrackEnrollmentProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracks:backfill-progress 
                            {--tenant= : ID específico de tenant/empresa} 
                            {--dry-run : Simular recálculo sem persistir alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula e atualiza de forma idempotente o progresso consolidado de todas as matrículas em trilhas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Modo DRY RUN ativo: nenhuma alteração será salva no banco de dados.');
        }

        $companiesQuery = Company::query();
        if ($tenantId) {
            $companiesQuery->where('id', $tenantId);
        }

        $companies = $companiesQuery->get();

        if ($companies->isEmpty()) {
            $this->error('Nenhuma empresa encontrada para processamento.');
            return self::FAILURE;
        }

        $totalProcessed = 0;
        $totalChanged = 0;

        foreach ($companies as $company) {
            $this->info("Processando empresa: {$company->name} (ID: {$company->id})");

            // Isola estritamente as matrículas da empresa para garantir isolamento multi-tenant
            $trackEnrollments = TrackEnrollment::where('company_id', $company->id)
                ->with(['track.publishedCourses'])
                ->get();

            foreach ($trackEnrollments as $te) {
                $totalProcessed++;
                $oldPercent = $te->progress_percent;
                $newPercent = $te->track ? $te->track->calculateUserProgress($te->user_id) : 0;
                $statusWillChange = ($newPercent >= 100 && $te->status !== 'completed') 
                    || ($newPercent < 100 && $te->status === 'completed');

                if ($oldPercent !== $newPercent || $statusWillChange) {
                    $totalChanged++;
                    $this->line("  - Matrícula #{$te->id} (Aluno #{$te->user_id}, Trilha #{$te->track_id}): {$oldPercent}% -> {$newPercent}%" . ($statusWillChange ? ' [Status alterado]' : ''));
                }

                if (!$isDryRun) {
                    $te->updateConsolidatedProgress();
                }
            }
        }

        $actionWord = $isDryRun ? 'identificadas para atualização' : 'atualizadas com sucesso';
        $this->newLine();
        $this->info("Concluído! {$totalProcessed} matrículas processadas; {$totalChanged} {$actionWord}.");

        return self::SUCCESS;
    }
}

