<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxonomy = [
            [
                'name' => 'Desenvolvimento Web',
                'slug' => 'dev-web',
                'icon' => 'code',
                'description' => 'Front-end, Back-end, WordPress e arquitetura de software corporativo.',
                'order' => 1,
                'subcategories' => [
                    ['name' => 'Tema WordPress', 'slug' => 'tema-wordpress', 'order' => 1],
                    ['name' => 'Bootstrap 5', 'slug' => 'bootstrap-5', 'order' => 2],
                    ['name' => 'PHP & Laravel', 'slug' => 'php-laravel', 'order' => 3],
                    ['name' => 'Gutenberg Blocks', 'slug' => 'gutenberg-blocks', 'order' => 4],
                ],
            ],
            [
                'name' => 'Design & UI/UX',
                'slug' => 'design-ui-ux',
                'icon' => 'paint-brush',
                'description' => 'Interfaces de usuário, Design Systems, Adobe e prototipagem interativa.',
                'order' => 2,
                'subcategories' => [
                    ['name' => 'Adobe Illustrator', 'slug' => 'adobe-illustrator', 'order' => 1],
                    ['name' => 'Teoria das Cores & Layout', 'slug' => 'teoria-cores-layout', 'order' => 2],
                    ['name' => 'Desenho SCORM Interativo', 'slug' => 'desenho-scorm-interativo', 'order' => 3],
                ],
            ],
            [
                'name' => 'Inteligência Artificial',
                'slug' => 'inteligencia-artificial',
                'icon' => 'sparkles',
                'description' => 'Modelos Preditivos de Evasão, LLMs, Engenharia de Prompt e Data Science.',
                'order' => 3,
                'subcategories' => [
                    ['name' => 'Modelos Preditivos de Evasão', 'slug' => 'modelos-preditivos-evasao', 'order' => 1],
                    ['name' => 'Engenharia de Prompt & LLMs', 'slug' => 'engenharia-prompt-llms', 'order' => 2],
                ],
            ],
            [
                'name' => 'Gestão & Liderança',
                'slug' => 'gestao-lideranca',
                'icon' => 'users',
                'description' => 'Liderança Executiva, Comunicação Não-Violenta, Gestão Ágil e LGPD.',
                'order' => 4,
                'subcategories' => [
                    ['name' => 'Liderança Estratégica', 'slug' => 'lideranca-estrategica', 'order' => 1],
                    ['name' => 'Feedback & Conflitos', 'slug' => 'feedback-conflitos', 'order' => 2],
                    ['name' => 'Scrum & Kanban Ágil', 'slug' => 'scrum-kanban-agil', 'order' => 3],
                    ['name' => 'Comunicação Não-Violenta', 'slug' => 'comunicacao-nao-violenta', 'order' => 4],
                    ['name' => 'Segurança da Informação & LGPD', 'slug' => 'seguranca-informacao-lgpd', 'order' => 5],
                ],
            ],
        ];

        foreach ($taxonomy as $catData) {
            $subs = $catData['subcategories'];
            unset($catData['subcategories']);

            $parent = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                array_merge($catData, ['is_active' => true])
            );

            foreach ($subs as $sub) {
                Category::updateOrCreate(
                    ['slug' => $sub['slug']],
                    array_merge($sub, [
                        'parent_id' => $parent->id,
                        'is_active' => true,
                    ])
                );
            }
        }

        // Se houver cursos sem categoria vinculada, associa inteligentemente às categorias
        $firstDev = Category::where('slug', 'dev-web')->first();
        $firstLeadership = Category::where('slug', 'gestao-lideranca')->first();

        if ($firstDev) {
            Course::withoutGlobalScopes()
                ->whereNull('category_id')
                ->where('title', 'like', '%HTML%')
                ->orWhere('title', 'like', '%Web%')
                ->orWhere('title', 'like', '%WordPress%')
                ->update(['category_id' => $firstDev->id]);
        }

        if ($firstLeadership) {
            Course::withoutGlobalScopes()
                ->whereNull('category_id')
                ->update(['category_id' => $firstLeadership->id]);
        }
    }
}
