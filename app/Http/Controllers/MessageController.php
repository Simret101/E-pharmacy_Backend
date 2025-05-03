<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
   
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
            'type' => 'nullable|in:text,image,file',
            'file_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'type' => $request->type ?? 'text',
            'file_url' => $request->file_url,
        ]);

        
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => 'Message sent successfully', 'data' => $message], 201);
    }

   
    public function getMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['messages' => $messages]);
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

  
    public function getConversationWithUser($userId)
    {
        $authUserId = auth()->id(); 
    
        $messages = Message::where(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $authUserId)
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $authUserId);
        })->with(['sender:id,name', 'receiver:id,name'])
          ->orderBy('created_at', 'asc')
          ->get();
    
        return response()->json(['messages' => $messages]);
    }
}
