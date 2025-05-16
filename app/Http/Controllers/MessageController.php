<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
            'type' => 'nullable|in:text,image,file',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048' // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $messageData = [
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'file_type' => $request->type ?? 'text'
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('uploads/messages', 'public');
                $messageData['file_path'] = Storage::disk('public')->url($path);
                $messageData['file_type'] = $file->getClientOriginalExtension();
                $messageData['file_name'] = $file->getClientOriginalName();
            }

            $message = Message::create($messageData);

            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send message',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getConversationWithUser($userId)
    {
        $authUserId = Auth::id();

        $messages = Message::where(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $authUserId)
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $authUserId);
        })
        ->with(['sender:id,name', 'receiver:id,name'])
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json(['messages' => $messages]);
    }

    public function getAllConversations()
    {
        $authUserId = Auth::id();

        $conversations = Message::where('sender_id', $authUserId)
            ->orWhere('receiver_id', $authUserId)
            ->with(['sender:id,name', 'receiver:id,name'])
            ->get()
            ->groupBy(function ($message) use ($authUserId) {
                return $message->sender_id == $authUserId ? $message->receiver_id : $message->sender_id;
            });

        return response()->json(['conversations' => $conversations]);
    }

    public function markAsRead($id)
    {
        $message = Message::find($id);

        if (!$message || $message->receiver_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized or not found'], 403);
        }

        $message->is_read = true;
        $message->save();

        return response()->json(['message' => 'Marked as read']);
    }

    public function deleteMessage($id)
    {
        $message = Message::find($id);

        if (!$message || $message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized or message not found'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }
}
