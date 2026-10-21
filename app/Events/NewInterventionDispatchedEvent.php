<?php

namespace App\Events;

use App\Models\AiIntervention;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewInterventionDispatchedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $interventionId;
    public int $userId;
    public string $message;
    public ?string $recommendedLessonTitle = null;
    public ?string $recommendedLessonUrl = null;

    /**
     * Create a new event instance.
     */
    public function __construct(AiIntervention $intervention)
    {
        $this->interventionId = $intervention->id;
        $this->userId = $intervention->user_id;
        $this->message = $intervention->message;

        $lesson = $intervention->recommendedLesson;
        if ($lesson) {
            $this->recommendedLessonTitle = $lesson->title;
            if ($lesson->course) {
                $this->recommendedLessonUrl = route('student.player', [
                    'course' => $lesson->course->slug,
                    'lesson' => $lesson->id,
                ]);
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'intervention.dispatched';
    }
}
