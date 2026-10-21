<?php

namespace App\Events;

use App\Models\RiskScore;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EvasionRiskAlertEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $riskScoreId;
    public int $companyId;
    public int $userId;
    public string $studentName;
    public int $score;
    public string $level;

    /**
     * Create a new event instance.
     */
    public function __construct(RiskScore $riskScore)
    {
        $this->riskScoreId = $riskScore->id;
        $this->companyId = $riskScore->company_id;
        $this->userId = $riskScore->user_id;
        $this->studentName = $riskScore->user?->name ?? 'Colaborador';
        $this->score = $riskScore->score;
        $this->level = $riskScore->level;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('company.' . $this->companyId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'evasion.risk.alert';
    }
}
