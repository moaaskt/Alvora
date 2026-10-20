<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Company\AiExecutiveInsights;
use App\Livewire\Instructor\CurriculumAiGenerator;
use App\Models\Company;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\User;
use App\Services\OpenRouterResilientService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class OpenRouterFallbackAndCurriculumGeneratorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_openrouter_cascades_to_fallback_model_on_first_model_failure(): void
    {
        $unique = uniqid();

        // 1º Modelo da lista (deepseek-r1) falha com 500
        // 2º Modelo da lista (llama-3.3-70b) responde 200 com sucesso
        $attempt = 0;
        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => function ($request) use (&$attempt) {
                $attempt++;
                if ($attempt === 1) {
                    return Http::response(['error' => 'Rate limit exceeded'], 500);
                }

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Resposta gerada com sucesso pelo modelo de contingência Llama 3.3.',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $service = new OpenRouterResilientService();
        $result = $service->complete('Explique boas práticas de onboarding corporativo.');

        $this->assertEquals(2, $result['attempts']);
        $this->assertEquals('meta-llama/llama-3.3-70b-instruct:free', $result['model_used']);
        $this->assertStringContainsString('Llama 3.3', $result['content']);
    }

    public function test_curriculum_ai_generator_creates_modules_and_lessons_in_course(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Curso IA {$unique}", 'status' => 'active']);

        $instructor = User::create([
            'name' => 'Instrutor Especialista',
            'email' => "instrutor_ia_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Segurança Cibernética para Bancos',
            'slug' => "sec-bank-{$unique}",
            'status' => 'draft',
        ]);

        $generatedJson = json_encode([
            'title' => 'Segurança Cibernética Avançada',
            'description' => 'Curso prático focado em proteção contra ataques e compliance BACEN.',
            'modules' => [
                [
                    'title' => 'Módulo 1: Proteção Perimetral e Firewalls',
                    'lessons' => [
                        [
                            'title' => 'Arquitetura Zero Trust',
                            'description' => 'Princípios de autenticação contínua.',
                            'duration_minutes' => 20,
                        ],
                        [
                            'title' => 'Mitigação de Phishing',
                            'description' => 'Treinando os colaboradores.',
                            'duration_minutes' => 15,
                        ],
                    ],
                ],
            ],
        ]);

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $generatedJson,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($instructor);

        Livewire::test(CurriculumAiGenerator::class, ['courseId' => $course->id])
            ->set('topic', 'Segurança Cibernética para Bancos')
            ->set('targetAudience', 'Analistas de TI e Segurança')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSee('Módulo 1: Proteção Perimetral e Firewalls')
            ->assertSee('Arquitetura Zero Trust')
            ->call('applyToCourse')
            ->assertRedirect(route('instructor.courses.curriculum', $course));

        $this->assertDatabaseHas('modules', [
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Módulo 1: Proteção Perimetral e Firewalls',
        ]);

        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'company_id' => $company->id,
            'title' => 'Arquitetura Zero Trust',
            'duration_minutes' => 20,
        ]);
    }

    public function test_ai_executive_insights_renders_metrics_and_summary(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa ROI {$unique}", 'status' => 'active']);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $admin = User::create([
            'name' => 'Diretor Executivo RH',
            'email' => "diretor_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $student = User::create([
            'name' => 'Colaborador ROI',
            'email' => "colab_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Diagnóstico Executivo: Observa-se que 100% dos colaboradores ativos mantêm engajamento estável com excelente projeção de ROI.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($admin);

        Livewire::test(AiExecutiveInsights::class)
            ->assertOk()
            ->assertSee('Total Colaboradores')
            ->assertSee('Observa-se que 100% dos colaboradores')
            ->call('refreshInsights')
            ->assertHasNoErrors();
    }
}
