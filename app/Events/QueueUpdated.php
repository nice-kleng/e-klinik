<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $queueId;
    public int $polyclinicId;
    public string $status;
    public string $action;
    public string $queueNumber;
    public ?string $patientName;
    public ?string $polyclinicName;
    public ?int $polyclinicQueueCount;
    public ?string $actionPayload;

    public function __construct(
        int $queueId,
        int $polyclinicId,
        string $status,
        string $action,
        string $queueNumber,
        ?string $patientName = null,
        ?string $polyclinicName = null,
        ?int $polyclinicQueueCount = null,
        ?string $actionPayload = null
    ) {
        $this->queueId = $queueId;
        $this->polyclinicId = $polyclinicId;
        $this->status = $status;
        $this->action = $action;
        $this->queueNumber = $queueNumber;
        $this->patientName = $patientName;
        $this->polyclinicName = $polyclinicName;
        $this->polyclinicQueueCount = $polyclinicQueueCount;
        $this->actionPayload = $actionPayload;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('queue'),
        ];
    }
}
