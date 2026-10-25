<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CertificateConcurrencyTest extends TestCase
{
    protected ?int $companyId = null;
    protected ?int $studentId = null;
    protected ?int $courseId = null;
    protected ?string $barrierDir = null;

    /**
     * Valida a integridade transacional contra condições de corrida reais com múltiplos processos paralelos.
     * Este teste exige MySQL 8 (InnoDB) para isolamento transacional e verificação de concorrência real.
     */
    public function test_concurrent_emission_with_independent_processes_guarantees_single_record_in_mysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped(
                'O teste de concorrência multi-processo real requer ambiente MySQL 8 (InnoDB). ' .
                'Execute no container: docker compose exec app php artisan test --filter=CertificateConcurrencyTest'
            );
        }

        // Setup dos dados em base para que subprocessos independentes do OS possam visualizar os registros
        $company = Company::create(['name' => 'Empresa Concorrencia Multiprocesso ' . uniqid(), 'status' => 'active']);
        $this->companyId = $company->id;

        $instructor = User::create([
            'name' => 'Instrutor Multiprocesso',
            'email' => 'inst.multi.' . uniqid() . '@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $student = User::create([
            'name' => 'Aluno Concorrente Real',
            'email' => 'student.multi.' . uniqid() . '@empresa.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);
        $this->studentId = $student->id;

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Concorrencia Multiprocesso MySQL 8',
            'slug' => 'curso-concorrencia-multiprocesso-' . uniqid(),
            'status' => 'published',
        ]);
        $this->courseId = $course->id;

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);

        // Diretório da barreira de sincronização entre processos
        $this->barrierDir = storage_path('framework/testing/barrier_' . uniqid());
        if (!is_dir($this->barrierDir)) {
            mkdir($this->barrierDir, 0777, true);
        }

        $basePath = addslashes(base_path());
        $startSignalPath = addslashes($this->barrierDir . '/start.signal');

        $numProcesses = 5;
        $processes = [];
        $pipes = [];

        $dbConfig = DB::connection()->getConfig();
        $dbDriver = $dbConfig['driver'] ?? 'mysql';
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = (string) ($dbConfig['port'] ?? 3306);
        $dbDatabase = $dbConfig['database'] ?? 'alvora_testing';
        $dbUsername = $dbConfig['username'] ?? 'alvora';
        $dbPassword = $dbConfig['password'] ?? 'secret';

        $envSetup = sprintf(
            'putenv("DB_CONNECTION=%s"); $_ENV["DB_CONNECTION"]="%s"; ' .
            'putenv("DB_HOST=%s"); $_ENV["DB_HOST"]="%s"; ' .
            'putenv("DB_PORT=%s"); $_ENV["DB_PORT"]="%s"; ' .
            'putenv("DB_DATABASE=%s"); $_ENV["DB_DATABASE"]="%s"; ' .
            'putenv("DB_USERNAME=%s"); $_ENV["DB_USERNAME"]="%s"; ' .
            'putenv("DB_PASSWORD=%s"); $_ENV["DB_PASSWORD"]="%s"; ',
            $dbDriver, $dbDriver,
            $dbHost, $dbHost,
            $dbPort, $dbPort,
            $dbDatabase, $dbDatabase,
            $dbUsername, $dbUsername,
            $dbPassword, $dbPassword
        );

        $procEnv = [];
        foreach (array_merge($_SERVER, $_ENV) as $k => $v) {
            if (is_scalar($v)) {
                $procEnv[(string) $k] = (string) $v;
            }
        }
        $procEnv['APP_ENV'] = 'testing';
        $procEnv['DB_CONNECTION'] = $dbDriver;
        $procEnv['DB_HOST'] = $dbHost;
        $procEnv['DB_PORT'] = $dbPort;
        $procEnv['DB_DATABASE'] = $dbDatabase;
        $procEnv['DB_USERNAME'] = $dbUsername;
        $procEnv['DB_PASSWORD'] = $dbPassword;

        // Inicializa os 5 subprocessos independentes com barreira de largada sincronizada
        for ($i = 0; $i < $numProcesses; $i++) {
            $readySignalPath = addslashes($this->barrierDir . "/ready_{$i}.signal");

            // O subprocesso:
            // 1. Inicializa o framework e conexão com a mesma base MySQL
            // 2. Pré-carrega instâncias (aquecendo cache/classes)
            // 3. Sinaliza que está pronto criando ready_{i}.signal
            // 4. Aguarda em loop rápido até a criação de start.signal (coordenação de largada)
            // 5. Executa getOrCreateCertificate simultaneamente com os demais processos
            $script = sprintf(
                '%s ' .
                'require "%s/vendor/autoload.php"; ' .
                '$app = require_once "%s/bootstrap/app.php"; ' .
                '$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); ' .
                '$user = App\Models\User::find(%d); ' .
                '$course = App\Models\Course::find(%d); ' .
                'touch("%s"); ' .
                'while (!file_exists("%s")) { usleep(200); } ' .
                '$cert = app(App\Services\CertificateService::class)->getOrCreateCertificate($user, $course); ' .
                'echo json_encode(["status" => "ok", "certificate_id" => $cert->id, "validation_code" => $cert->validation_code]);',
                $envSetup,
                $basePath,
                $basePath,
                $student->id,
                $course->id,
                $readySignalPath,
                $startSignalPath
            );

            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $cmd = [PHP_BINARY, '-r', $script];
            $proc = proc_open($cmd, $descriptors, $pipes[$i], base_path(), $procEnv);
            $this->assertIsResource($proc, "Falha ao iniciar processo concorrente {$i}");
            $processes[$i] = $proc;
        }

        // Aguarda todos os 5 processos sinalizarem prontidão (barreira montada)
        $timeout = microtime(true) + 15;
        while (count(glob($this->barrierDir . '/ready_*.signal')) < $numProcesses) {
            if (microtime(true) > $timeout) {
                $stderrLogs = [];
                foreach ($pipes as $idx => $p) {
                    $stderrLogs[$idx] = stream_get_contents($p[2]);
                }
                $this->fail("Subprocessos concorrentes não atingiram a barreira a tempo. STDERR: " . json_encode($stderrLogs));
            }
            usleep(1000);
        }

        // Dispara a largada simultânea para todos os processos concorrentes
        touch($this->barrierDir . '/start.signal');

        $outputs = [];
        $errors = [];

        // Aguarda a finalização de todos os processos paralelos
        foreach ($processes as $i => $proc) {
            $out = stream_get_contents($pipes[$i][1]);
            $err = stream_get_contents($pipes[$i][2]);
            fclose($pipes[$i][0]);
            fclose($pipes[$i][1]);
            fclose($pipes[$i][2]);
            $exitCode = proc_close($proc);

            $decoded = json_decode(trim($out), true);
            $outputs[$i] = $decoded;
            $errors[$i] = [
                'exitCode' => $exitCode,
                'stderr' => $err,
                'stdout' => $out,
            ];

            // Critério: Nenhuma requisição concorrente resulta em erro inesperado
            $this->assertEquals(0, $exitCode, "Processo {$i} falhou com erro: " . ($err ?: $out));
            $this->assertIsArray($decoded, "Processo {$i} não retornou JSON válido: {$out}");
            $this->assertEquals('ok', $decoded['status'] ?? null);
        }

        // Critério: Todos os resultados retornados correspondem ao mesmo certificado persistido
        $firstId = $outputs[0]['certificate_id'];
        $firstCode = $outputs[0]['validation_code'];
        $this->assertNotNull($firstId);

        foreach ($outputs as $i => $res) {
            $this->assertEquals($firstId, $res['certificate_id'], "Processo {$i} retornou certificate_id divergente.");
            $this->assertEquals($firstCode, $res['validation_code'], "Processo {$i} retornou validation_code divergente.");
        }

        // Critério: Exatamente um certificado para a combinação prevista de usuário, curso e tenant no banco
        $totalCertificates = DB::table('certificates')
            ->where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->count();

        $this->assertEquals(1, $totalCertificates, 'Deve existir rigorosamente 1 único certificado para o par (user_id, course_id).');
    }

    protected function tearDown(): void
    {
        // Limpeza dos artefatos da barreira
        if ($this->barrierDir && is_dir($this->barrierDir)) {
            foreach (glob($this->barrierDir . '/*') as $file) {
                @unlink($file);
            }
            @rmdir($this->barrierDir);
        }

        if ($this->courseId) {
            DB::table('certificates')->where('course_id', $this->courseId)->delete();
            DB::table('enrollments')->where('course_id', $this->courseId)->delete();
            DB::table('courses')->where('id', $this->courseId)->delete();
        }

        if ($this->studentId) {
            DB::table('users')->where('id', $this->studentId)->delete();
        }

        if ($this->companyId) {
            DB::table('companies')->where('id', $this->companyId)->delete();
        }

        parent::tearDown();
    }
}
