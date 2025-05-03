<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender:id,name', 'receiver:id,name']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat.' . $this->message->receiver_id);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
