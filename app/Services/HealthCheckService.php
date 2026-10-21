<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckService
{
    /**
     * Run all system health checks.
     *
     * @return array<string, mixed>
     */
    public function check(): array
    {
        $database = $this->checkDatabase();
        $cache = $this->checkCache();
        $storage = $this->checkStorage();
        $system = $this->getSystemInfo();

        $overallStatus = 'ok';

        if ($database['status'] !== 'healthy') {
            $overallStatus = 'error';
        } elseif ($cache['status'] !== 'healthy' || $storage['status'] !== 'healthy') {
            $overallStatus = 'degraded';
        }

        return [
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'database' => $database,
            'cache' => $cache,
            'storage' => $storage,
            'system' => $system,
        ];
    }

    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            $start = microtime(true);
            $key = 'health_check_ping_' . uniqid();
            Cache::put($key, 'pong', 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $retrieved === 'pong' ? 'healthy' : 'unhealthy',
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $filename = 'health_storage_check_' . uniqid() . '.txt';
            Storage::disk('local')->put($filename, 'healthy');
            $content = Storage::disk('local')->get($filename);
            Storage::disk('local')->delete($filename);

            return [
                'status' => $content === 'healthy' ? 'healthy' : 'unhealthy',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'environment' => app()->environment(),
        ];
    }
}
