<?php

namespace Tests\Feature;

use App\Livewire\Instructor\CourseEditor;
use App\Models\Category;
use App\Models\Company;
use App\Models\Course;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseWysiwygDescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $instructor;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Empresa Teste WYSIWYG',
            'slug' => 'empresa-teste-wysiwyg',
            'cnpj' => '12.345.678/0001-90',
            'subscription_status' => 'active',
        ]);

        $this->instructor = User::create([
            'company_id' => $this->company->id,
            'name' => 'Professor Carlos Silva',
            'email' => 'carlos.wysiwyg@alvora.local',
            'password' => bcrypt('password'),
            'role' => 'instructor',
        ]);

        $this->category = Category::create([
            'name' => 'Tecnologia e Produto',
            'slug' => 'tecnologia-e-produto',
            'is_active' => true,
        ]);
    }

    public function test_html_sanitizer_preserves_safe_tags_and_formats(): void
    {
        $input = '<h2>Ementa do Treinamento</h2><p>Aprenda <strong>Bootstrap</strong> e <em>Tailwind</em>.</p><ul><li>Módulo 1</li><li>Módulo 2</li></ul><p><a href="https://alvora.com.br">Saiba mais</a></p>';
        $cleaned = HtmlSanitizer::clean($input);

        $this->assertStringContainsString('<h2>Ementa do Treinamento</h2>', $cleaned);
        $this->assertStringContainsString('<strong>Bootstrap</strong>', $cleaned);
        $this->assertStringContainsString('<em>Tailwind</em>', $cleaned);
        $this->assertStringContainsString('<ul><li>Módulo 1</li><li>Módulo 2</li></ul>', $cleaned);
        $this->assertStringContainsString('href="https://alvora.com.br"', $cleaned);
        $this->assertStringContainsString('rel="noopener noreferrer"', $cleaned);
        $this->assertStringContainsString('target="_blank"', $cleaned);
    }

    public function test_html_sanitizer_strips_scripts_and_malicious_event_handlers(): void
    {
        $malicious = '<h2>Título</h2><script>alert("hacked")</script><p onload="doEvil()" onclick="steal()">Texto com <img src="x" onerror="badCode()" /> perigo</p><a href="javascript:alert(1)">Link Hacker</a>';
        $cleaned = HtmlSanitizer::clean($malicious);

        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringNotContainsString('alert("hacked")', $cleaned);
        $this->assertStringNotContainsString('onload=', $cleaned);
        $this->assertStringNotContainsString('onclick=', $cleaned);
        $this->assertStringNotContainsString('onerror=', $cleaned);
        $this->assertStringNotContainsString('javascript:', $cleaned);
        $this->assertStringContainsString('<h2>Título</h2>', $cleaned);
    }

    public function test_html_sanitizer_returns_empty_string_for_empty_quill_content(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean(''));
        $this->assertSame('', HtmlSanitizer::clean('<p><br></p>'));
        $this->assertSame('', HtmlSanitizer::clean('<p></p>'));
    }

    public function test_instructor_can_create_course_with_rich_wysiwyg_description(): void
    {
        $this->actingAs($this->instructor);

        $richDescription = '<h2>Objetivos do Curso</h2><p>Capacitação <strong>prática</strong> e orientada a resultados corporativos.</p><ul><li>Tópico 1</li><li>Tópico 2</li></ul>';

        Livewire::test(CourseEditor::class)
            ->set('title', 'Desenvolvimento de Temas Modernos')
            ->set('category_id', $this->category->id)
            ->set('instructor_id', $this->instructor->id)
            ->set('status', 'draft')
            ->set('description', $richDescription)
            ->call('save')
            ->assertHasNoErrors();

        $course = Course::withoutGlobalScopes()
            ->where('title', 'Desenvolvimento de Temas Modernos')
            ->first();

        $this->assertNotNull($course);
        $this->assertStringContainsString('<h2>Objetivos do Curso</h2>', $course->description);
        $this->assertStringContainsString('<strong>prática</strong>', $course->description);
        $this->assertStringContainsString('<ul><li>Tópico 1</li><li>Tópico 2</li></ul>', $course->description);
    }

    public function test_instructor_can_update_course_description_with_html_sanitization(): void
    {
        $this->actingAs($this->instructor);

        $course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'title' => 'Gestão de Mudança Organizacional',
            'slug' => 'gestao-de-mudanca-organizacional',
            'description' => '<p>Descrição inicial</p>',
            'status' => 'draft',
        ]);

        $updatedDescriptionWithXss = '<h2>Nova Ementa</h2><script>alert("xss")</script><p onclick="hack()">Conteúdo corporativo <strong>revisado</strong>.</p>';

        Livewire::test(CourseEditor::class, ['course' => $course])
            ->set('description', $updatedDescriptionWithXss)
            ->call('save')
            ->assertHasNoErrors();

        $course->refresh();

        $this->assertStringContainsString('<h2>Nova Ementa</h2>', $course->description);
        $this->assertStringContainsString('<strong>revisado</strong>', $course->description);
        $this->assertStringNotContainsString('<script>', $course->description);
        $this->assertStringNotContainsString('onclick=', $course->description);
    }

    public function test_public_course_page_renders_rich_wysiwyg_description(): void
    {
        $course = Course::create([
            'company_id' => $this->company->id,
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'title' => 'Liderança Estratégica em EdTech',
            'slug' => 'lideranca-estrategica-em-edtech',
            'description' => '<h2>Visão Executiva</h2><p>Treinamento para <strong>gestores</strong> seniores.</p><ul><li>Meta 1</li><li>Meta 2</li></ul>',
            'status' => 'published',
        ]);

        $response = $this->get(route('courses.public.show', $course->slug));

        $response->assertStatus(200);
        $response->assertSee('<h2>Visão Executiva</h2>', false);
        $response->assertSee('<strong>gestores</strong>', false);
        $response->assertSee('<li>Meta 1</li>', false);
    }
}

