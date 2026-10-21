<?php

namespace Tests\Feature;

use App\Services\HealthCheckService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckAndObservabilityTest extends TestCase
{
    public function test_health_check_endpoint_returns_ok_when_services_are_healthy(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'database' => ['status', 'latency_ms'],
                'cache' => ['status', 'latency_ms'],
                'storage' => ['status'],
                'system' => ['php_version', 'laravel_version', 'memory_usage_mb', 'environment'],
            ]);

        $this->assertEquals('ok', $response->json('status'));
        $this->assertEquals('healthy', $response->json('database.status'));
        $this->assertEquals('healthy', $response->json('cache.status'));
        $this->assertEquals('healthy', $response->json('storage.status'));
    }

    public function test_health_check_endpoint_is_publicly_accessible(): void
    {
        // Garante que não exige auth/middleware de login
        $this->assertGuest();
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
    }

    public function test_health_check_endpoint_returns_503_when_database_is_failing(): void
    {
        // Mock do HealthCheckService simulando falha crítica no banco de dados
        $mockService = $this->createMock(HealthCheckService::class);
        $mockService->method('check')->willReturn([
            'status' => 'error',
            'timestamp' => now()->toIso8601String(),
            'database' => [
                'status' => 'unhealthy',
                'error' => 'Connection refused: MySQL server has gone away',
            ],
            'cache' => [
                'status' => 'healthy',
                'latency_ms' => 0.5,
            ],
            'storage' => [
                'status' => 'healthy',
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage_mb' => 24.5,
                'environment' => 'testing',
            ],
        ]);

        $this->app->instance(HealthCheckService::class, $mockService);

        $response = $this->getJson('/api/health');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'error',
                'database' => [
                    'status' => 'unhealthy',
                    'error' => 'Connection refused: MySQL server has gone away',
                ],
            ]);
    }

    public function test_health_check_endpoint_returns_200_degraded_when_only_cache_is_unhealthy(): void
    {
        $mockService = $this->createMock(HealthCheckService::class);
        $mockService->method('check')->willReturn([
            'status' => 'degraded',
            'timestamp' => now()->toIso8601String(),
            'database' => [
                'status' => 'healthy',
                'latency_ms' => 1.2,
            ],
            'cache' => [
                'status' => 'unhealthy',
                'error' => 'Redis connection timed out',
            ],
            'storage' => [
                'status' => 'healthy',
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage_mb' => 24.5,
                'environment' => 'testing',
            ],
        ]);

        $this->app->instance(HealthCheckService::class, $mockService);

        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'degraded',
                'cache' => [
                    'status' => 'unhealthy',
                ],
            ]);
    }

    public function test_observability_configurations_are_defined_and_valid(): void
    {
        // Validação da config Telescope
        $this->assertNotNull(config('telescope.enabled'));
        $this->assertEquals('database', config('telescope.driver'));
        $this->assertArrayHasKey('watchers', config('telescope'));

        // Validação da config Sentry
        $this->assertEquals(0.2, config('sentry.traces_sample_rate'));
        $this->assertEquals(0.1, config('sentry.profiles_sample_rate'));
        $this->assertFalse(config('sentry.send_default_pii'));
    }
}
