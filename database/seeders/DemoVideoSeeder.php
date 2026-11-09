<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Jobs\CalculateEvasionRiskJob;
use App\Models\AiIntervention;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningTrack;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\RiskScore;
use App\Models\Subscription;
use App\Models\TrackEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('Iniciando DemoVideoSeeder para Acme Corporation Brasil...');

        // 1. Obter ou criar a Empresa de Demonstração
        $company = Company::firstOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            [
                'name' => 'Acme Corporation Brasil',
                'plan' => 'enterprise',
                'status' => 'active',
                'max_users' => 100, // Garantir capacidade para os 40+ colaboradores
            ]
        );

        if ($company->max_users < 100) {
            $company->update(['max_users' => 100]);
        }

        // Assinatura ativa da empresa
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

        // 2. Garantir usuários essenciais (Admin, RH, Instrutores)
        $admin = User::firstOrCreate(
            ['email' => 'admin@alvora.com.br'],
            [
                'name' => 'Administrador Alvora',
                'password' => $defaultPassword,
                'role' => UserRole::ADMIN_ALVORA,
                'company_id' => null,
            ]
        );

        // Autenticar temporariamente como Super Admin Alvora durante o seeding
        // Isso assegura que global scopes (CourseAccessScope, TenantScope) permitam firstOrCreate/update sem 1=0
        \Illuminate\Support\Facades\Auth::login($admin);

        $rh = User::firstOrCreate(
            ['email' => 'rh@acme.com.br'],
            [
                'name' => 'Mariana Gestora de RH',
                'password' => $defaultPassword,
                'role' => UserRole::COMPANY_ADMIN,
                'company_id' => $company->id,
                'department' => 'Operações',
                'job_title' => 'Gerente de Gente & Gestão',
            ]
        );

        // Garantir vínculo de gestão em company_users para autorização canManageCompany (RBAC)
        DB::table('company_users')->updateOrInsert(
            [
                'company_id' => $company->id,
                'user_id' => $rh->id,
            ],
            [
                'role' => 'company_admin',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $instructor = User::firstOrCreate(
            ['email' => 'professor@acme.com.br'],
            [
                'name' => 'Prof. Carlos Eduardo',
                'password' => $defaultPassword,
                'role' => UserRole::INSTRUCTOR,
                'company_id' => $company->id,
                'job_title' => 'Instrutor Chefe',
            ]
        );

        $instructor2 = User::firstOrCreate(
            ['email' => 'helena.valenca@acme.com.br'],
            [
                'name' => 'Dra. Helena Valença',
                'password' => $defaultPassword,
                'role' => UserRole::INSTRUCTOR,
                'company_id' => $company->id,
                'job_title' => 'Instrutora de Dados',
            ]
        );

        // 3. Garantir os 3 Cursos com Módulos, Aulas e Vídeos Embed/MP4 funcionais
        $demoVideoUrl = url('/videos/demo-lesson.mp4');

        $course1 = Course::firstOrCreate(
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

        $courses = collect([$course1, $course2, $course3]);

        // Módulos e Aulas com vídeo corrigido (evitar "vídeo indisponível" no player)
        $courseLessons = [];

        foreach ($courses as $cIdx => $course) {
            $module = Module::firstOrCreate(
                ['course_id' => $course->id, 'title' => 'Módulo 1: Fundamentos Práticos'],
                [
                    'company_id' => $company->id,
                    'description' => 'Conteúdo inicial estruturado para nivelamento das equipes.',
                    'order' => 1,
                ]
            );

            // Aula 1
            $lesson1 = Lesson::firstOrCreate(
                ['module_id' => $module->id, 'title' => "Aula 01: Introdução ao {$course->title}"],
                [
                    'course_id' => $course->id,
                    'company_id' => $company->id,
                    'description' => 'Apresentação dos conceitos essenciais e aplicabilidade no ambiente de trabalho.',
                    'video_url' => $demoVideoUrl,
                    'duration_minutes' => 12,
                    'order' => 1,
                    'is_free_preview' => false,
                ]
            );
            // Atualizar caso já existisse com rickroll ou url quebrada
            if ($lesson1->video_url !== $demoVideoUrl && str_contains($lesson1->video_url, 'dQw4w9WgXcQ')) {
                $lesson1->update(['video_url' => $demoVideoUrl]);
            }

            // Aula 2
            $lesson2 = Lesson::firstOrCreate(
                ['module_id' => $module->id, 'title' => "Aula 02: Ferramentas e Execução no Dia a Dia"],
                [
                    'course_id' => $course->id,
                    'company_id' => $company->id,
                    'description' => 'Métodos práticos, rotinas de alto impacto e estudos de caso corporativos.',
                    'video_url' => $demoVideoUrl,
                    'duration_minutes' => 18,
                    'order' => 2,
                    'is_free_preview' => false,
                ]
            );

            // Atualizar qualquer aula legada do curso 1 que tivesse a URL antiga
            Lesson::where('course_id', $course->id)
                ->where('video_url', 'like', '%dQw4w9WgXcQ%')
                ->update(['video_url' => $demoVideoUrl]);

            $courseLessons[$course->id] = [$lesson1, $lesson2];

            // Quiz do curso para suporte ao cálculo de risco
            Quiz::firstOrCreate(
                ['company_id' => $company->id, 'course_id' => $course->id, 'title' => "Avaliação: {$course->title}"],
                [
                    'module_id' => $module->id,
                    'passing_score' => 70,
                ]
            );
        }

        // 4. Trilhas de Aprendizagem vinculadas aos Departamentos
        $tracksDef = [
            'Comercial' => 'Trilha de Excelência Comercial & Vendas B2B',
            'Tecnologia' => 'Trilha de Engenharia & Inovação Digital',
            'Operações' => 'Trilha de Gestão Operacional & Eficiência',
            'Atendimento' => 'Trilha de Sucesso do Cliente & Atendimento Humanizado',
        ];

        $tracks = [];
        foreach ($tracksDef as $dept => $trackTitle) {
            $track = LearningTrack::firstOrCreate(
                ['company_id' => $company->id, 'slug' => Str::slug($trackTitle)],
                [
                    'title' => $trackTitle,
                    'description' => "Trilha corporativa direcionada para capacitação da equipe de {$dept}.",
                    'department' => $dept,
                    'is_mandatory' => true,
                ]
            );
            $tracks[$dept] = $track;

            // Vincular cursos à trilha
            foreach ($courses as $ord => $c) {
                DB::table('track_courses')->updateOrInsert(
                    ['track_id' => $track->id, 'course_id' => $c->id],
                    ['order' => $ord + 1, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        // 5. Lista de 40 colaboradores brasileiros distribuídos nas 4 equipes
        // Distribuição de risco desejada:
        // Alto Risco (70-100): 5 colaboradores
        // Médio Risco (40-69): 10 colaboradores
        // Baixo Risco (0-39): 25 colaboradores
        $collaborators = [
            // === ALTO RISCO (5 colaboradores): inatividade 15-40 dias, notas baixas no quiz ===
            [
                'name' => 'Beatriz Mendes',
                'email' => 'beatriz.mendes@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Executiva de Contas Jr',
                'target_risk' => 'high',
                'days_inactive' => 28,
                'quiz_score' => 20,
                'progress' => [15, 0, 0],
            ],
            [
                'name' => 'Rodrigo Vasconcelos',
                'email' => 'rodrigo.vasconcelos@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Desenvolvedor Frontend',
                'target_risk' => 'high',
                'days_inactive' => 35,
                'quiz_score' => 15,
                'progress' => [20, 0, 0],
            ],
            [
                'name' => 'Juliana Albuquerque',
                'email' => 'juliana.albuquerque@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Analista de Processos',
                'target_risk' => 'high',
                'days_inactive' => 22,
                'quiz_score' => 25,
                'progress' => [10, 0, 0],
            ],
            [
                'name' => 'Felipe Guimarães',
                'email' => 'felipe.guimaraes@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Especialista de Suporte',
                'target_risk' => 'high',
                'days_inactive' => 38,
                'quiz_score' => 10,
                'progress' => [15, 0, 0],
            ],
            [
                'name' => 'Camila Cavalcanti',
                'email' => 'camila.cavalcanti@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'SDR Pré-vendas',
                'target_risk' => 'high',
                'days_inactive' => 19,
                'quiz_score' => 30,
                'progress' => [25, 0, 0],
            ],

            // === MÉDIO RISCO (10 colaboradores): inatividade 5-8 dias, 2 aulas concluídas, notas medianas ===
            [
                'name' => 'Thiago Moreira',
                'email' => 'thiago.moreira@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Engenheiro de DevOps',
                'target_risk' => 'medium',
                'days_inactive' => 5,
                'quiz_score' => 65,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Fernanda Ribeiro',
                'email' => 'fernanda.ribeiro@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Coordenadora de Logística',
                'target_risk' => 'medium',
                'days_inactive' => 6,
                'quiz_score' => 60,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Lucas Peixoto',
                'email' => 'lucas.peixoto@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Analista de CX',
                'target_risk' => 'medium',
                'days_inactive' => 5,
                'quiz_score' => 65,
                'progress' => [50, 45, 30],
            ],
            [
                'name' => 'Amanda Nogueira',
                'email' => 'amanda.nogueira@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Gerente de Contas B2B',
                'target_risk' => 'medium',
                'days_inactive' => 6,
                'quiz_score' => 60,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Gabriel Farias',
                'email' => 'gabriel.farias@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Desenvolvedor Backend',
                'target_risk' => 'medium',
                'days_inactive' => 5,
                'quiz_score' => 60,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Renata Toledo',
                'email' => 'renata.toledo@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Supervisora de Operações',
                'target_risk' => 'medium',
                'days_inactive' => 6,
                'quiz_score' => 65,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Bruno Siqueira',
                'email' => 'bruno.siqueira@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Analista de Helpdesk Pleno',
                'target_risk' => 'medium',
                'days_inactive' => 5,
                'quiz_score' => 60,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Larissa Antunes',
                'email' => 'larissa.antunes@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Inside Sales',
                'target_risk' => 'medium',
                'days_inactive' => 6,
                'quiz_score' => 65,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Diego Campelo',
                'email' => 'diego.campelo@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'QA Engineer',
                'target_risk' => 'medium',
                'days_inactive' => 5,
                'quiz_score' => 60,
                'progress' => [50, 40, 20],
            ],
            [
                'name' => 'Priscila Meireles',
                'email' => 'priscila.meireles@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Analista de Planejamento',
                'target_risk' => 'medium',
                'days_inactive' => 6,
                'quiz_score' => 60,
                'progress' => [50, 40, 20],
            ],

            // === BAIXO RISCO (25 colaboradores): atividade recente (0-2 dias), bons progressos e boas notas ===
            [
                'name' => 'Lucas Colaborador',
                'email' => 'aluno@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Executivo de Vendas Enterprise',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [100, 85, 70],
            ],
            [
                'name' => 'Aline Barreto',
                'email' => 'aline.barreto@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Tech Lead',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 100,
                'progress' => [100, 100, 80],
            ],
            [
                'name' => 'Matheus Carneiro',
                'email' => 'matheus.carneiro@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Gerente de Projetos',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 90,
                'progress' => [90, 80, 75],
            ],
            [
                'name' => 'Carla Pimentel',
                'email' => 'carla.pimentel@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Líder de Customer Success',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [100, 90, 85],
            ],
            [
                'name' => 'Eduardo Castilho',
                'email' => 'eduardo.castilho@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Gerente Regional de Vendas',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 85,
                'progress' => [80, 70, 60],
            ],
            [
                'name' => 'Mariana Duarte',
                'email' => 'mariana.duarte@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Engenheira de Dados Sênior',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [95, 90, 70],
            ],
            [
                'name' => 'Vinicius Macedo',
                'email' => 'vinicius.macedo@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Analista de Qualidade e Compliance',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 90,
                'progress' => [85, 75, 65],
            ],
            [
                'name' => 'Tatiane Guedes',
                'email' => 'tatiane.guedes@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Supervisora de Suporte N2',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 100,
                'progress' => [100, 95, 90],
            ],
            [
                'name' => 'Gustavo Fontes',
                'email' => 'gustavo.fontes@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Consultor Comercial',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 85,
                'progress' => [75, 65, 55],
            ],
            [
                'name' => 'Patricia Camargo',
                'email' => 'patricia.camargo@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Arquiteta de Soluções Cloud',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [90, 85, 80],
            ],
            [
                'name' => 'Rafael Moura',
                'email' => 'rafael.moura@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Coordenador Administrativo',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 90,
                'progress' => [80, 70, 70],
            ],
            [
                'name' => 'Isabela Dornelles',
                'email' => 'isabela.dornelles@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Analista de Onboarding',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [95, 85, 80],
            ],
            [
                'name' => 'Leonardo Pinheiro',
                'email' => 'leonardo.pinheiro@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Especialista em Negócios B2B',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 85,
                'progress' => [80, 75, 65],
            ],
            [
                'name' => 'Vanessa Lins',
                'email' => 'vanessa.lins@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Product Owner',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 90,
                'progress' => [85, 80, 75],
            ],
            [
                'name' => 'Caio Henrique',
                'email' => 'caio.henrique@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Analista de Supply Chain',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 85,
                'progress' => [75, 70, 60],
            ],
            [
                'name' => 'Debora Santana',
                'email' => 'debora.santana@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Analista de Retenção e Churn',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [90, 85, 80],
            ],
            [
                'name' => 'Marcelo Aguiar',
                'email' => 'marcelo.aguiar@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Account Executive',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 85,
                'progress' => [80, 65, 60],
            ],
            [
                'name' => 'Sabrina Neves',
                'email' => 'sabrina.neves@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Desenvolvedora Mobile Flutter',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [90, 85, 75],
            ],
            [
                'name' => 'Henrique Vilela',
                'email' => 'henrique.vilela@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Analista de Melhoria Contínua',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 90,
                'progress' => [85, 75, 70],
            ],
            [
                'name' => 'Natalia Borges',
                'email' => 'natalia.borges@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Analista de Qualidade de Atendimento',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 100,
                'progress' => [100, 90, 85],
            ],
            [
                'name' => 'Igor Fontenele',
                'email' => 'igor.fontenele@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Key Account Manager',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 85,
                'progress' => [80, 70, 65],
            ],
            [
                'name' => 'Bianca Esteves',
                'email' => 'bianca.esteves@acme.com.br',
                'department' => 'Tecnologia',
                'job_title' => 'Especialista em Segurança da Informação',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 95,
                'progress' => [95, 90, 80],
            ],
            [
                'name' => 'Daniel Brandão',
                'email' => 'daniel.brandao@acme.com.br',
                'department' => 'Operações',
                'job_title' => 'Coordenador de Facilities',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 90,
                'progress' => [85, 75, 65],
            ],
            [
                'name' => 'Thais Alencar',
                'email' => 'thais.alencar@acme.com.br',
                'department' => 'Atendimento',
                'job_title' => 'Gerente de Atendimento e Suporte',
                'target_risk' => 'low',
                'days_inactive' => 0,
                'quiz_score' => 100,
                'progress' => [100, 95, 90],
            ],
            [
                'name' => 'Victor Hugo Silveira',
                'email' => 'victor.silveira@acme.com.br',
                'department' => 'Comercial',
                'job_title' => 'Coordenador Comercial Regional',
                'target_risk' => 'low',
                'days_inactive' => 1,
                'quiz_score' => 90,
                'progress' => [85, 80, 75],
            ],
        ];

        // Caso exista Beatriz Mendes legada como 'aluno.risco@acme.com.br', atualizar para manter consistência
        User::where('email', 'aluno.risco@acme.com.br')->update([
            'email' => 'beatriz.mendes@acme.com.br',
            'name' => 'Beatriz Mendes',
            'department' => 'Comercial',
            'job_title' => 'Executiva de Contas Jr',
        ]);

        $createdUsers = [];

        foreach ($collaborators as $collab) {
            $user = User::firstOrCreate(
                ['email' => $collab['email']],
                [
                    'name' => $collab['name'],
                    'password' => $defaultPassword,
                    'role' => UserRole::STUDENT,
                    'company_id' => $company->id,
                    'department' => $collab['department'],
                    'job_title' => $collab['job_title'],
                    'is_active' => true,
                ]
            );

            // Atualiza departamento e cargo caso o registro já existisse
            $user->update([
                'name' => $collab['name'],
                'department' => $collab['department'],
                'job_title' => $collab['job_title'],
                'company_id' => $company->id,
                'role' => UserRole::STUDENT,
            ]);

            // Definir inatividade via updated_at (respeitando o requisito)
            $inactiveDays = $collab['days_inactive'];
            $activityTimestamp = now()->subDays($inactiveDays);
            DB::table('users')->where('id', $user->id)->update([
                'updated_at' => $activityTimestamp,
                'created_at' => now()->subDays(max(45, $inactiveDays + 15)),
            ]);

            // Matrícula na Trilha do seu departamento
            $track = $tracks[$collab['department']] ?? null;
            if ($track) {
                TrackEnrollment::firstOrCreate(
                    ['company_id' => $company->id, 'user_id' => $user->id, 'track_id' => $track->id],
                    [
                        'status' => 'active',
                        'progress_percent' => (int) round(collect($collab['progress'])->avg()),
                    ]
                );
            }

            // Matrícula nos 3 Cursos com progresso variado
            foreach ($courses as $cIdx => $course) {
                $pct = $collab['progress'][$cIdx] ?? 0;
                $enrollmentDate = now()->subDays(max(30, $inactiveDays + 10));

                $enrollment = Enrollment::firstOrCreate(
                    ['company_id' => $company->id, 'user_id' => $user->id, 'course_id' => $course->id],
                    [
                        'status' => $pct >= 100 ? 'completed' : 'active',
                        'progress_percent' => $pct,
                        'enrolled_at' => $enrollmentDate,
                        'completed_at' => $pct >= 100 ? now()->subDays(2) : null,
                    ]
                );
                $enrollment->update([
                    'progress_percent' => $pct,
                    'status' => $pct >= 100 ? 'completed' : 'active',
                ]);
                DB::table('enrollments')->where('id', $enrollment->id)->update([
                    'created_at' => $enrollmentDate,
                ]);

                // Progresso das aulas correspondentes
                $lessons = $courseLessons[$course->id] ?? [];
                if (!empty($lessons)) {
                    $lesson1 = $lessons[0];
                    $lesson2 = $lessons[1] ?? null;

                    if ($pct >= 40) {
                        LessonProgress::updateOrCreate(
                            ['company_id' => $company->id, 'user_id' => $user->id, 'lesson_id' => $lesson1->id],
                            [
                                'completed_at' => $activityTimestamp,
                                'last_watched_second' => 720,
                            ]
                        );
                    } elseif ($pct > 0) {
                        LessonProgress::updateOrCreate(
                            ['company_id' => $company->id, 'user_id' => $user->id, 'lesson_id' => $lesson1->id],
                            [
                                'completed_at' => null,
                                'last_watched_second' => (int) round(($pct / 100) * 720),
                            ]
                        );
                    }

                    if ($pct >= 80 && $lesson2) {
                        LessonProgress::updateOrCreate(
                            ['company_id' => $company->id, 'user_id' => $user->id, 'lesson_id' => $lesson2->id],
                            [
                                'completed_at' => $activityTimestamp,
                                'last_watched_second' => 1080,
                            ]
                        );
                    }
                }

                // Tentativa de Quiz vinculada
                $quiz = Quiz::where('course_id', $course->id)->first();
                if ($quiz && $collab['quiz_score'] !== null) {
                    QuizAttempt::updateOrCreate(
                        ['company_id' => $company->id, 'user_id' => $user->id, 'quiz_id' => $quiz->id],
                        [
                            'score' => $collab['quiz_score'],
                            'passed' => $collab['quiz_score'] >= 70,
                            'answers_json' => json_encode(['nota' => $collab['quiz_score']]),
                            'completed_at' => $activityTimestamp,
                        ]
                    );
                }
            }

            $createdUsers[] = [
                'model' => $user,
                'target_risk' => $collab['target_risk'],
                'data' => $collab,
            ];
        }

        $this->command?->info("40 colaboradores cadastrados com sucesso nas 4 equipes.");

        // 6. Rodar o CalculateEvasionRiskJob para calcular os scores de forma 100% consistente
        $this->command?->info('Executando CalculateEvasionRiskJob para atualizar os scores no modelo analítico...');
        (new CalculateEvasionRiskJob(companyId: $company->id))->handle(app(\App\Services\EvasionRiskCalculatorService::class));

        // 7. Configurar exatamente 8 intervenções já disparadas (com mensagem gerada e status)
        // Selecionar os 5 alunos de alto risco + 3 de médio risco
        $this->command?->info('Configurando as 8 intervenções disparadas pela IA...');

        // Limpar intervenções anteriores da empresa para garantir idempotência exata de 8 intervenções
        AiIntervention::where('company_id', $company->id)->delete();

        $intervenedUsers = collect($createdUsers)
            ->filter(fn($item) => in_array($item['target_risk'], ['high', 'medium']))
            ->take(8);

        $interventionTemplates = [
            [
                'status' => 'pending',
                'message' => 'Olá, %NAME%! Notamos que faz alguns dias que você não acessa a Trilha de %DEPT%. Separamos um resumo rápido da Aula 01 em 5 minutos para você retomar no seu ritmo sem sobrecarga. Podemos te apoiar?',
                'read_at' => null,
            ],
            [
                'status' => 'viewed',
                'message' => 'Oi, %NAME%, tudo bem? Identificamos que o quiz sobre Fundamentos teve alguns desafios. Disponibilizamos um material complementar exclusivo e o Copilot IA está disponível no player para tirar dúvidas conceituais!',
                'read_at' => now()->subDays(2),
            ],
            [
                'status' => 'actioned',
                'message' => 'Prezado(a) %NAME%, o RH da Acme preparou um plano flexível de 15 minutos diários para a Trilha de %DEPT%. Você já completou 20% do trajeto e está muito perto do primeiro certificado corporativo!',
                'read_at' => now()->subDays(5),
            ],
            [
                'status' => 'pending',
                'message' => 'Olá, %NAME%! Sentimos sua falta nas aulas desta semana. O módulo prático foi atualizado com novos estudos de caso aplicados à sua área (%DEPT%). Conte com a gente para destravar seu progresso.',
                'read_at' => null,
            ],
            [
                'status' => 'viewed',
                'message' => 'Oi, %NAME%! Que tal assistir a próxima aula prática direto do seu celular? Apenas 10 minutos bastam para concluir o módulo e manter sua constância de aprendizado.',
                'read_at' => now()->subDay(),
            ],
            [
                'status' => 'actioned',
                'message' => 'Olá, %NAME%! Parabéns pelo engajamento inicial! Seu tutor IA identificou uma ótima oportunidade para você revisar os tópicos da última aula com um simulado rápido sem pressão de nota.',
                'read_at' => now()->subDays(4),
            ],
            [
                'status' => 'pending',
                'message' => 'Olá, %NAME%, percebemos um intervalo maior desde seu último acesso. Sabia que a conclusão deste treinamento corporativo conta pontos no seu PDI de %DEPT%? Veja o cronograma sugerido.',
                'read_at' => null,
            ],
            [
                'status' => 'viewed',
                'message' => 'Oi, %NAME%! Seu gestor e a equipe da Alvora estão à disposição para tirar qualquer dúvida técnica sobre o conteúdo. Vamos juntos retomar o ritmo?',
                'read_at' => now()->subHours(12),
            ],
        ];

        $firstLesson = Lesson::where('course_id', $course1->id)->first();

        foreach ($intervenedUsers->values() as $idx => $item) {
            $user = $item['model'];
            $template = $interventionTemplates[$idx % count($interventionTemplates)];
            $riskScore = RiskScore::where('user_id', $user->id)->latest('id')->first();

            $msg = str_replace(
                ['%NAME%', '%DEPT%'],
                [$user->name, $user->department ?? 'Sua Área'],
                $template['message']
            );

            AiIntervention::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                ],
                [
                    'risk_score_id' => $riskScore?->id,
                    'recommended_lesson_id' => $firstLesson?->id,
                    'message' => $msg,
                    'status' => $template['status'],
                    'read_at' => $template['read_at'],
                    'created_at' => now()->subDays(rand(1, 6)),
                ]
            );
        }

        $this->command?->info("DemoVideoSeeder concluído com sucesso!");
    }
}
