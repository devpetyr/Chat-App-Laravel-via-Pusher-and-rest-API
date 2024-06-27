<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('MessageSent event dispatched: ' . $message->id);
    }

    public function broadcastOn()
    {
        $sortedIds = [$this->message->sender_id, $this->message->receiver_id];
        sort($sortedIds);
        $roomId = 'room_' . $sortedIds[0] . '_' . $sortedIds[1];

        return new PrivateChannel('ChatAppForYBL.' . $roomId);
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
