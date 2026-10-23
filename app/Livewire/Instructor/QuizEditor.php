<?php

namespace App\Livewire\Instructor;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.instructor', ['title' => 'Gestão de Avaliações — ALVORA', 'breadcrumb' => 'Avaliação'])]
class QuizEditor extends Component
{
    public Course $course;
    public ?Quiz $quiz = null;

    public string $title = '';
    public int $passing_score = 70;
    public ?int $module_id = null;

    public array $questions = [];

    /**
     * Mount component for new or existing quiz.
     */
    public function mount(?Course $course = null, ?Quiz $quiz = null): void
    {
        if ($quiz && $quiz->exists) {
            $this->quiz = $quiz;
            $this->course = ($course && $course->exists) ? $course : $quiz->course;
            $this->title = $quiz->title;
            $this->passing_score = $quiz->passing_score;
            $this->module_id = $quiz->module_id;

            $this->questions = $quiz->questions()->with('options')->get()->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'explanation' => $q->explanation ?? '',
                    'options' => $q->options->map(function ($o) {
                        return [
                            'id' => $o->id,
                            'option_text' => $o->option_text,
                            'is_correct' => (bool) $o->is_correct,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        } else {
            $this->course = $course ?? ($quiz?->course ?? new Course());
            // Template default com 1 pergunta e 2 opções
            $this->questions = [
                [
                    'id' => null,
                    'question_text' => '',
                    'explanation' => '',
                    'options' => [
                        ['id' => null, 'option_text' => '', 'is_correct' => true],
                        ['id' => null, 'option_text' => '', 'is_correct' => false],
                    ],
                ],
            ];
        }
    }

    /**
     * Add a new question block.
     */
    public function addQuestion(): void
    {
        $this->questions[] = [
            'id' => null,
            'question_text' => '',
            'explanation' => '',
            'options' => [
                ['id' => null, 'option_text' => '', 'is_correct' => true],
                ['id' => null, 'option_text' => '', 'is_correct' => false],
            ],
        ];
    }

    /**
     * Remove question by index.
     */
    public function removeQuestion(int $index): void
    {
        if (count($this->questions) > 1) {
            unset($this->questions[$index]);
            $this->questions = array_values($this->questions);
        }
    }

    /**
     * Add an option to a question.
     */
    public function addOption(int $questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = [
            'id' => null,
            'option_text' => '',
            'is_correct' => false,
        ];
    }

    /**
     * Remove an option from a question.
     */
    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (count($this->questions[$questionIndex]['options']) > 2) {
            unset($this->questions[$questionIndex]['options'][$optionIndex]);
            $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
        }
    }

    /**
     * Set a single option as the correct answer for a question.
     */
    public function setCorrectOption(int $questionIndex, int $optionIndex): void
    {
        foreach ($this->questions[$questionIndex]['options'] as $idx => &$option) {
            $option['is_correct'] = ($idx === $optionIndex);
        }
    }

    /**
     * Save quiz, questions and options atomically.
     */
    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'passing_score' => 'required|integer|min:1|max:100',
            'module_id' => 'nullable|exists:modules,id',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
        ], [
            'title.required' => 'O título do quiz é obrigatório.',
            'passing_score.required' => 'A nota mínima de aprovação é obrigatória.',
            'questions.*.question_text.required' => 'O enunciado da questão não pode ficar em branco.',
            'questions.*.options.*.option_text.required' => 'O texto da alternativa não pode ficar em branco.',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user) {
            $quiz = $this->quiz ?? new Quiz();
            $quiz->company_id = $user->company_id;
            $quiz->course_id = $this->course->id;
            $quiz->module_id = $this->module_id;
            $quiz->title = $this->title;
            $quiz->passing_score = $this->passing_score;
            $quiz->save();

            // Sincronizar perguntas
            $existingQuestionIds = [];
            foreach ($this->questions as $order => $qData) {
                $question = isset($qData['id'])
                    ? QuizQuestion::find($qData['id'])
                    : new QuizQuestion();

                if (!$question) {
                    $question = new QuizQuestion();
                }

                $question->company_id = $user->company_id;
                $question->quiz_id = $quiz->id;
                $question->question_text = $qData['question_text'];
                $question->explanation = $qData['explanation'] ?? null;
                $question->order = $order + 1;
                $question->save();

                $existingQuestionIds[] = $question->id;

                // Sincronizar opções
                $existingOptionIds = [];
                foreach ($qData['options'] as $oData) {
                    $option = isset($oData['id'])
                        ? QuizOption::find($oData['id'])
                        : new QuizOption();

                    if (!$option) {
                        $option = new QuizOption();
                    }

                    $option->quiz_question_id = $question->id;
                    $option->option_text = $oData['option_text'];
                    $option->is_correct = (bool) $oData['is_correct'];
                    $option->save();

                    $existingOptionIds[] = $option->id;
                }

                // Deletar opções removidas
                $question->options()->whereNotIn('id', $existingOptionIds)->delete();
            }

            // Deletar perguntas removidas
            $quiz->questions()->whereNotIn('id', $existingQuestionIds)->delete();

            $this->quiz = $quiz;
        });

        session()->flash('success', 'Quiz pedagógico salvo com sucesso!');

        return redirect()->route('instructor.courses.edit', [
            'course' => $this->course->id,
            'tab' => 'quizzes',
        ]);
    }

    /**
     * Render the quiz editor view.
     */
    public function render(): View
    {
        $modules = $this->course->modules()->orderBy('order')->get();

        return view('livewire.instructor.quiz-editor', [
            'modules' => $modules,
        ]);
    }
}
