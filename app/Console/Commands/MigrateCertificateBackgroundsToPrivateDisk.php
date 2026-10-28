<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateCertificateBackgroundsToPrivateDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificates:migrate-backgrounds-to-private {--dry-run : Executa em modo de simulação sem alterar arquivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra com segurança as artes de fundo de certificados do disco público para o armazenamento privado local';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info("Iniciando migração de artes de certificados para armazenamento privado local...");
        if ($dryRun) {
            $this->warn("MODO DE SIMULAÇÃO ATIVO (--dry-run). Nenhum arquivo será alterado.");
        }

        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');

        $directory = 'certificate-backgrounds';
        if (!$publicDisk->exists($directory)) {
            $this->info("Nenhum diretório '{$directory}' encontrado no disco público. Migração concluída.");
            return self::SUCCESS;
        }

        $allFiles = $publicDisk->allFiles($directory);
        if (empty($allFiles)) {
            $this->info("Nenhum arquivo encontrado em '{$directory}' no disco público.");
            return self::SUCCESS;
        }

        $this->info(sprintf("Encontrados %d arquivo(s) no disco público para verificação.", count($allFiles)));

        $copiedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($allFiles as $filePath) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $this->line("  [IGNORADO] Arquivo não suportado: {$filePath}");
                $skippedCount++;
                continue;
            }

            if ($privateDisk->exists($filePath)) {
                $this->line("  [JÁ EXISTE] Arquivo já presente no disco privado: {$filePath}");
                $skippedCount++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [SIMULAÇÃO] Copiaria: {$filePath} (público -> privado)");
                $copiedCount++;
                continue;
            }

            try {
                $fileContents = $publicDisk->get($filePath);
                $stored = $privateDisk->put($filePath, $fileContents);

                if ($stored) {
                    $this->info("  [COPIADO] {$filePath} migrado com sucesso para o disco privado.");
                    $copiedCount++;
                } else {
                    $this->error("  [ERRO] Falha ao gravar arquivo no disco privado: {$filePath}");
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $this->error("  [EXCEÇÃO] Erro ao migrar {$filePath}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $this->newLine();
        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Arquivos verificados', count($allFiles)],
                ['Arquivos migrados com sucesso', $copiedCount],
                ['Arquivos já existentes / ignorados', $skippedCount],
                ['Falhas de migração', $failedCount],
                ['Arquivos públicos preservados (não apagados)', count($allFiles)],
            ]
        );

        $this->info("Atenção: Os arquivos originais do disco público foram rigorosamente preservados.");
        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
