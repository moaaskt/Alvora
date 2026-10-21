<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Student\AiTutorDrawer;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Company;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\User;
use App\Services\GroqAiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiTutorChatTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_open_drawer_and_send_message_with_groq_response(): void
    {
        $unique = uniqid();
        $company = Company::create(['name' => "Empresa Tutor {$unique}", 'status' => 'active']);

        Subscription::create([
            'company_id' => $company->id,
            'plan' => 'pro',
            'status' => 'active',
            'amount' => 1290.00,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $student = User::create([
            'name' => 'Aluno Curioso',
            'email' => "curioso_{$unique}@empresa.com",
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $student->id,
            'title' => 'Introdução à IA Corporativa',
            'slug' => "intro-ia-{$unique}",
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Fundamentos',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'O que são LLMs',
            'description' => 'Conceito básico de modelos de linguagem de grande escala.',
            'order' => 1,
            'duration_minutes' => 10,
        ]);

        // Simula resposta da API Groq
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'LLMs são redes neurais treinadas em grandes volumes de texto para compreender e gerar linguagem natural.',
                        ],
                    ],
                ],
                'usage' => [
                    'total_tokens' => 85,
                ],
            ], 200),
        ]);

        $this->actingAs($student);

        Livewire::test(AiTutorDrawer::class)
            ->call('openDrawer', $course->id, $lesson->id)
            ->assertSet('isOpen', true)
            ->assertSet('currentLessonId', $lesson->id)
            ->set('userMessage', 'O que significa a sigla LLM?')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSee('O que significa a sigla LLM?')
            ->assertSee('LLMs são redes neurais');

        $this->assertDatabaseHas('ai_conversations', [
            'user_id' => $student->id,
            'company_id' => $company->id,
            'lesson_id' => $lesson->id,
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'sender' => 'user',
            'content' => 'O que significa a sigla LLM?',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'sender' => 'assistant',
            'content' => 'LLMs são redes neurais treinadas em grandes volumes de texto para compreender e gerar linguagem natural.',
        ]);

        Http::assertSent(function ($request) use ($lesson) {
            $messages = $request['messages'];
            $systemMsg = collect($messages)->firstWhere('role', 'system')['content'] ?? '';
            return str_contains($systemMsg, $lesson->title);
        });
    }

    public function test_ai_conversations_respect_tenant_isolation(): void
    {
        $unique = uniqid();
        $companyA = Company::create(['name' => "Tenant Chat A {$unique}", 'status' => 'active']);
        $companyB = Company::create(['name' => "Tenant Chat B {$unique}", 'status' => 'active']);

        $studentA = User::create([
            'name' => 'Aluno A',
            'email' => "chata_{$unique}@alfa.com",
            'password' => bcrypt('password'),
            'company_id' => $companyA->id,
            'role' => UserRole::STUDENT,
        ]);

        $studentB = User::create([
            'name' => 'Aluno B',
            'email' => "chatb_{$unique}@beta.com",
            'password' => bcrypt('password'),
            'company_id' => $companyB->id,
            'role' => UserRole::STUDENT,
        ]);

        $convA = AiConversation::create([
            'company_id' => $companyA->id,
            'user_id' => $studentA->id,
            'title' => 'Conversa Empresa A',
        ]);

        $convB = AiConversation::create([
            'company_id' => $companyB->id,
            'user_id' => $studentB->id,
            'title' => 'Conversa Empresa B',
        ]);

        $this->actingAs($studentA);
        $conversations = AiConversation::all();

        $this->assertTrue($conversations->contains('id', $convA->id));
        $this->assertFalse($conversations->contains('id', $convB->id));
    }
}
