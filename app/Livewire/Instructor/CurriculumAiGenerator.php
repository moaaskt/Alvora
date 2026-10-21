<?php

namespace App\Livewire\Instructor;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Services\OpenRouterResilientService;
use Livewire\Component;

class CurriculumAiGenerator extends Component
{
    public int $courseId;
    public string $topic = '';
    public string $targetAudience = 'Colaboradores Corporativos';
    public bool $isGenerating = false;
    public ?array $generatedCurriculum = null;
    public ?string $modelUsed = null;

    public function mount(int $courseId): void
    {
        $this->courseId = $courseId;
        $course = Course::findOrFail($courseId);
        $this->topic = $course->title;
    }

    public function generate(): void
    {
        $this->validate([
            'topic' => 'required|string|min:4|max:255',
            'targetAudience' => 'required|string|min:3|max:255',
        ]);

        $this->isGenerating = true;

        $service = app(OpenRouterResilientService::class);
        $curriculum = $service->generateStructuredCurriculum($this->topic, $this->targetAudience);

        $this->generatedCurriculum = $curriculum;
        $this->modelUsed = $curriculum['_meta']['model_used'] ?? 'OpenRouter Fallback';
        $this->isGenerating = false;
    }

    public function applyToCourse(): void
    {
        if (empty($this->generatedCurriculum) || empty($this->generatedCurriculum['modules'])) {
            return;
        }

        $course = Course::findOrFail($this->courseId);
        $companyId = $course->company_id;

        // Atualiza a descrição caso esteja vazia
        if (empty($course->description) && !empty($this->generatedCurriculum['description'])) {
            $course->update(['description' => $this->generatedCurriculum['description']]);
        }

        $lastModuleOrder = Module::where('course_id', $course->id)->max('order') ?? 0;

        foreach ($this->generatedCurriculum['modules'] as $modIndex => $modData) {
            $module = Module::create([
                'company_id' => $companyId,
                'course_id' => $course->id,
                'title' => $modData['title'],
                'order' => $lastModuleOrder + $modIndex + 1,
            ]);

            if (!empty($modData['lessons'])) {
                foreach ($modData['lessons'] as $lessIndex => $lessData) {
                    Lesson::create([
                        'company_id' => $companyId,
                        'course_id' => $course->id,
                        'module_id' => $module->id,
                        'title' => $lessData['title'],
                        'description' => $lessData['description'] ?? null,
                        'duration_minutes' => $lessData['duration_minutes'] ?? 15,
                        'order' => $lessIndex + 1,
                    ]);
                }
            }
        }

        session()->flash('success', 'Grade gerada pela IA aplicada com sucesso à grade do curso!');
        $this->redirect(route('instructor.courses.curriculum', $course));
    }

    public function render()
    {
        $course = Course::findOrFail($this->courseId);

        return view('livewire.instructor.curriculum-ai-generator', [
            'course' => $course,
        ]);
    }
}
