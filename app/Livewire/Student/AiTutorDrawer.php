<?php

namespace App\Livewire\Student;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Lesson;
use App\Services\GroqAiService;
use Livewire\Attributes\On;
use Livewire\Component;

class AiTutorDrawer extends Component
{
    public bool $isOpen = false;
    public string $userMessage = '';
    public ?int $conversationId = null;
    public ?int $currentCourseId = null;
    public ?int $currentLessonId = null;
    public bool $isSending = false;

    #[On('open-ai-tutor')]
    public function openDrawer(?int $courseId = null, ?int $lessonId = null): void
    {
        $this->isOpen = true;
        if ($courseId) $this->currentCourseId = $courseId;
        if ($lessonId) $this->currentLessonId = $lessonId;

        $this->loadOrCreateConversation();
    }

    #[On('set-tutor-context')]
    public function setContext(?int $courseId, ?int $lessonId): void
    {
        $this->currentCourseId = $courseId;
        $this->currentLessonId = $lessonId;
    }

    public function toggleDrawer(): void
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->loadOrCreateConversation();
        }
    }

    public function closeDrawer(): void
    {
        $this->isOpen = false;
    }

    public function clearConversation(): void
    {
        $this->conversationId = null;
        $this->loadOrCreateConversation(forceNew: true);
    }

    public function sendMessage(): void
    {
        $messageText = trim($this->userMessage);
        if (empty($messageText)) {
            return;
        }

        $this->userMessage = '';
        $this->isSending = true;

        $conversation = $this->loadOrCreateConversation();

        // 1. Save user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => 'user',
            'content' => $messageText,
        ]);

        // 2. Build history for LLM
        $recentMessages = $conversation->messages()
            ->latest()
            ->take(8)
            ->get()
            ->reverse()
            ->map(fn($m) => [
                'role' => $m->sender === 'user' ? 'user' : 'assistant',
                'content' => $m->content,
            ])
            ->toArray();

        // 3. Request reply from Groq
        $context = [
            'course_id' => $this->currentCourseId,
            'lesson_id' => $this->currentLessonId,
            'user_name' => auth()->user()?->name,
        ];

        $response = app(GroqAiService::class)->chat($recentMessages, $context);

        // 4. Save assistant reply
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => 'assistant',
            'content' => $response['content'],
            'tokens_used' => $response['tokens_used'],
        ]);

        $this->isSending = false;
        $this->dispatch('message-received');
    }

    protected function loadOrCreateConversation(bool $forceNew = false): AiConversation
    {
        $user = auth()->user();

        if (!$forceNew && $this->conversationId) {
            $existing = AiConversation::where('id', $this->conversationId)
                ->where('user_id', $user->id)
                ->first();
            if ($existing) return $existing;
        }

        // Search for existing conversation for this specific lesson or recent
        if (!$forceNew && $this->currentLessonId) {
            $lessonConv = AiConversation::where('user_id', $user->id)
                ->where('lesson_id', $this->currentLessonId)
                ->latest()
                ->first();

            if ($lessonConv) {
                $this->conversationId = $lessonConv->id;
                return $lessonConv;
            }
        }

        $lesson = $this->currentLessonId ? Lesson::find($this->currentLessonId) : null;
        $title = $lesson ? "Dúvidas: {$lesson->title}" : "Conversa com Tutor IA";

        $conversation = AiConversation::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'title' => $title,
            'course_id' => $this->currentCourseId,
            'lesson_id' => $this->currentLessonId,
        ]);

        $this->conversationId = $conversation->id;
        return $conversation;
    }

    public function render()
    {
        $messages = collect();
        if ($this->conversationId) {
            $messages = AiMessage::where('conversation_id', $this->conversationId)
                ->oldest()
                ->get();
        }

        $currentLesson = $this->currentLessonId ? Lesson::find($this->currentLessonId) : null;

        return view('livewire.student.ai-tutor-drawer', [
            'messages' => $messages,
            'currentLesson' => $currentLesson,
        ]);
    }
}
