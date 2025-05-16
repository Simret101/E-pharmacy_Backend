<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'file_path',
        'file_type',
        'file_name',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
