<?php

namespace App\Livewire\Student;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QuizRunner extends Component
{
    public Quiz $quiz;
    public array $userAnswers = []; // question_id => option_id
    public bool $submitted = false;
    public ?QuizAttempt $lastAttempt = null;
    public int $score = 0;
    public bool $passed = false;
    public int $correctCount = 0;
    public int $totalQuestions = 0;

    /**
     * Mount component with quiz data.
     */
    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz->load(['questions.options', 'course']);
        $this->totalQuestions = $this->quiz->questions->count();

        $user = Auth::user();
        $this->lastAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->first();

        if ($this->lastAttempt) {
            $this->score = $this->lastAttempt->score;
            $this->passed = (bool) $this->lastAttempt->passed;
            $this->userAnswers = $this->lastAttempt->answers_json ?? [];
            $this->submitted = true;
        }
    }

    /**
     * Select option for a question.
     */
    public function selectOption(int $questionId, int $optionId): void
    {
        if ($this->submitted) {
            return;
        }

        $this->userAnswers[$questionId] = $optionId;
    }

    /**
     * Submit answers and calculate score with immediate correction.
     */
    public function submit(): void
    {
        if ($this->submitted) {
            return;
        }

        if (count($this->userAnswers) < $this->totalQuestions) {
            session()->flash('warning', 'Por favor, responda a todas as questões antes de finalizar a tentativa.');
            return;
        }

        $correct = 0;
        foreach ($this->quiz->questions as $question) {
            $selectedOptionId = $this->userAnswers[$question->id] ?? null;
            $correctOption = $question->options->firstWhere('is_correct', true);

            if ($correctOption && $selectedOptionId == $correctOption->id) {
                $correct++;
            }
        }

        $this->correctCount = $correct;
        $this->score = $this->totalQuestions > 0
            ? (int) round(($correct / $this->totalQuestions) * 100)
            : 0;

        $this->passed = ($this->score >= $this->quiz->passing_score);
        $this->submitted = true;

        $user = Auth::user();

        $attempt = QuizAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'quiz_id' => $this->quiz->id,
            'score' => $this->score,
            'passed' => $this->passed,
            'answers_json' => $this->userAnswers,
            'completed_at' => now(),
        ]);

        $this->lastAttempt = $attempt;

        if ($this->passed) {
            session()->flash('success', "Parabéns! Você foi aprovado com {$this->score}% de aproveitamento!");
        } else {
            session()->flash('error', "Você obteve {$this->score}%. A nota mínima é {$this->quiz->passing_score}%. Não desanime, revise as aulas e tente novamente!");
        }
    }

    /**
     * Allow student to retry the quiz.
     */
    public function retry(): void
    {
        $this->userAnswers = [];
        $this->submitted = false;
        $this->score = 0;
        $this->passed = false;
    }

    /**
     * Render the student quiz runner view.
     */
    public function render(): View
    {
        return view('livewire.student.quiz-runner');
    }
}
