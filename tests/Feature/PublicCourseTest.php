<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicCourseTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;
    private User $instructor;
    private Course $course;
    private Module $module;
    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::firstOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            ['name' => 'Acme Corporation Brasil', 'status' => 'active']
        );

        $this->instructor = User::firstOrCreate(
            ['email' => 'instrutor.publico@acme.com.br'],
            [
                'name' => 'Prof. Carlos Eduardo',
                'password' => bcrypt('password'),
                'company_id' => $this->company->id,
                'role' => UserRole::INSTRUCTOR,
            ]
        );

        $category = Category::firstOrCreate(
            ['slug' => 'lideranca-executiva'],
            ['company_id' => $this->company->id, 'name' => 'Liderança Executiva']
        );

        $this->course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Inteligência Artificial e Automação para Líderes Corporativos',
            'slug' => 'ia-para-lideres-corporativos-' . uniqid(),
            'description' => 'Aprenda a liderar a transformação digital e integrar modelos generativos na tomada de decisão estratégica.',
            'thumbnail' => 'https://alvora.test/capa.jpg',
            'status' => 'published',
        ]);

        $this->module = Module::create([
            'company_id' => $this->company->id,
            'course_id' => $this->course->id,
            'title' => 'Módulo 1: Fundamentos de IA Generativa & LLMs Corporativos',
            'order' => 1,
        ]);

        $this->lesson = Lesson::create([
            'company_id' => $this->company->id,
            'course_id' => $this->course->id,
            'module_id' => $this->module->id,
            'title' => 'O Panorama Executivo da IA Generativa em Grandes Organizações',
            'order' => 1,
            'duration_minutes' => 18,
            'is_free_preview' => true,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function test_public_course_page_renders_published_course_successfully(): void
    {
        $response = $this->get(route('courses.public.show', $this->course->slug));

        $response->assertStatus(200);
        $response->assertSee($this->course->title);
        $response->assertSee('Prof. Carlos Eduardo');
        $response->assertSee('Capacitar Minha Equipe');
        $response->assertSee('Módulo 1: Fundamentos de IA Generativa');
        $response->assertSee('Aula Grátis');
        $response->assertSee('Garantia Corporativa Alvora');
        $response->assertSee('https://alvora.test/capa.jpg');
        // Navbar check
        $response->assertSee('Programas &amp; Cursos', false);
        // Footer check
        $response->assertSee('ALVORA Educação Corporativa');
    }

    public function test_public_course_page_returns_404_for_draft_course(): void
    {
        $draftCourse = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Curso Não Publicado em Rascunho',
            'slug' => 'curso-rascunho-' . uniqid(),
            'status' => 'draft',
        ]);

        $response = $this->get(route('courses.public.show', $draftCourse->slug));

        $response->assertStatus(404);
    }

    public function test_public_course_page_returns_404_for_non_existent_slug(): void
    {
        $response = $this->get('/cursos/slug-completamente-inexistente-123');

        $response->assertStatus(404);
    }

    public function test_enrolled_student_sees_continue_watching_button(): void
    {
        $student = User::create([
            'name' => 'Aluno Matriculado',
            'email' => 'aluno.matriculado.' . uniqid() . '@acme.com.br',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'role' => UserRole::STUDENT,
        ]);

        Enrollment::create([
            'company_id' => $this->company->id,
            'user_id' => $student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('courses.public.show', $this->course->slug));

        $response->assertStatus(200);
        $response->assertSee('Continuar Assistindo no Painel');
        $response->assertSee(route('student.player', $this->course->slug));
    }

    public function test_landing_page_course_catalog_links_to_public_course_detail(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('courses.public.show', $this->course->slug));
        $response->assertSee('https://alvora.test/capa.jpg');
    }
}
