<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\RiskScore;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo credentials and structure.
     */
    public function run(): void
    {
        // 1. Criar Empresa de Demonstração
        $company = Company::firstOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            [
                'name' => 'Acme Corporation Brasil',
                'plan' => 'enterprise',
                'status' => 'active',
                'max_users' => 50,
            ]
        );

        // Assinatura ativa da empresa (evita bloqueio no middleware CheckSubscriptionStatus)
        Subscription::firstOrCreate(
            ['company_id' => $company->id],
            [
                'mp_preapproval_id' => 'PREAPP-DEMO-2026',
                'plan' => 'enterprise',
                'status' => 'active',
                'amount' => 1490.00,
                'billing_cycle' => 'monthly',
                'current_period_start' => now()->startOfMonth(),
                'current_period_end' => now()->addMonth(),
            ]
        );

        $defaultPassword = Hash::make('password123');

        // 2. Super Admin da Plataforma Alvora (Acesso irrestrito e faturamento global)
        $admin = User::firstOrCreate(
            ['email' => 'admin@alvora.com.br'],
            [
                'name' => 'Administrador Alvora',
                'password' => $defaultPassword,
                'role' => UserRole::ADMIN_ALVORA,
                'company_id' => null,
            ]
        );

        // 3. Gestor de RH (Company Admin)
        $rh = User::firstOrCreate(
            ['email' => 'rh@acme.com.br'],
            [
                'name' => 'Mariana Gestora de RH',
                'password' => $defaultPassword,
                'role' => UserRole::COMPANY_ADMIN,
                'company_id' => $company->id,
            ]
        );

        // 4. Instrutores Corporativos
        $instructor = User::firstOrCreate(
            ['email' => 'professor@acme.com.br'],
            [
                'name' => 'Prof. Carlos Eduardo',
                'password' => $defaultPassword,
                'role' => UserRole::INSTRUCTOR,
                'company_id' => $company->id,
            ]
        );

        $instructor2 = User::firstOrCreate(
            ['email' => 'helena.valenca@acme.com.br'],
            [
                'name' => 'Dra. Helena Valença',
                'password' => $defaultPassword,
                'role' => UserRole::INSTRUCTOR,
                'company_id' => $company->id,
            ]
        );

        // 5. Aluno / Colaborador
        $student = User::firstOrCreate(
            ['email' => 'aluno@acme.com.br'],
            [
                'name' => 'Lucas Colaborador',
                'password' => $defaultPassword,
                'role' => UserRole::STUDENT,
                'company_id' => $company->id,
            ]
        );

        // 6. Aluno com risco de evasão (para teste da Sala de Situação do RH)
        $atRiskStudent = User::firstOrCreate(
            ['email' => 'aluno.risco@acme.com.br'],
            [
                'name' => 'Beatriz Mendes (Alerta)',
                'password' => $defaultPassword,
                'role' => UserRole::STUDENT,
                'company_id' => $company->id,
            ]
        );

        // 7. Cursos Executivos Demo com Módulos e Aulas
        $course = Course::firstOrCreate(
            [
                'company_id' => $company->id,
                'slug' => 'fundamentos-de-lideranca-e-alta-performance',
            ],
            [
                'instructor_id' => $instructor->id,
                'title' => 'Fundamentos de Liderança e Alta Performance',
                'description' => 'Programa corporativo completo para desenvolvimento de líderes de equipe, comunicação estratégica e gestão humanizada.',
                'status' => 'published',
            ]
        );

        $course2 = Course::firstOrCreate(
            [
                'company_id' => $company->id,
                'slug' => 'inteligencia-de-dados-e-analytics-para-decisoes-estrategicas',
            ],
            [
                'instructor_id' => $instructor2->id,
                'title' => 'Inteligência de Dados e Analytics para Decisões Estratégicas',
                'description' => 'Capacitação prática em visualização de métricas, modelos preditivos e tomada de decisão orientada a dados para gestores de alta performance.',
                'status' => 'published',
            ]
        );

        $course3 = Course::firstOrCreate(
            [
                'company_id' => $company->id,
                'slug' => 'cultura-de-feedback-e-gestao-de-times-hibridos',
            ],
            [
                'instructor_id' => $instructor->id,
                'title' => 'Cultura de Feedback e Gestão de Times Híbridos',
                'description' => 'Metodologias práticas para alinhamento de expectativas, rituais de liderança remota, avaliação contínua e retenção de talentos estratégicos.',
                'status' => 'published',
            ]
        );

        $module = Module::firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Módulo 1: Introdução à Liderança'],
            [
                'company_id' => $company->id,
                'description' => 'Bases essenciais de comunicação e liderança situacional.',
                'order' => 1,
            ]
        );

        $lesson = Lesson::firstOrCreate(
            ['module_id' => $module->id, 'title' => 'Aula 01: O Papel do Líder no Século XXI'],
            [
                'course_id' => $course->id,
                'company_id' => $company->id,
                'description' => 'Compreenda a transição de especialista técnico para gestor de pessoas.',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration_minutes' => 15,
                'order' => 1,
                'is_free_preview' => false,
            ]
        );

        // Matricular alunos
        Enrollment::firstOrCreate(
            ['user_id' => $student->id, 'course_id' => $course->id],
            [
                'company_id' => $company->id,
                'status' => 'active',
                'progress_percent' => 50,
                'enrolled_at' => now()->subDays(5),
            ]
        );

        Enrollment::firstOrCreate(
            ['user_id' => $atRiskStudent->id, 'course_id' => $course->id],
            [
                'company_id' => $company->id,
                'status' => 'active',
                'progress_percent' => 10,
                'enrolled_at' => now()->subDays(20),
            ]
        );

        // Score de risco de evasão demonstrativo para o aluno em risco
        RiskScore::firstOrCreate(
            ['user_id' => $atRiskStudent->id, 'company_id' => $company->id],
            [
                'score' => 84,
                'level' => 'high',
                'breakdown' => [
                    'inactivity' => ['score' => 100, 'weight' => 35, 'weighted' => 35],
                    'stagnation' => ['score' => 90, 'weight' => 25, 'weighted' => 22.5],
                    'quiz' => ['score' => 70, 'weight' => 20, 'weighted' => 14],
                    'peer_deviation' => ['score' => 62, 'weight' => 20, 'weighted' => 12.4],
                ],
                'calculated_at' => now(),
            ]
        );
    }
}
