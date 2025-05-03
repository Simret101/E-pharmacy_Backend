<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use App\Customs\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_without:file|string|nullable',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the receiver is valid based on user roles
        $receiver = User::findOrFail($request->receiver_id);
        $sender = Auth::user();

        // Admin can chat with anyone
        if ($sender->is_role === 0) {
            // No restrictions for admin
        }
        // Pharmacist can chat with admin and patients
        elseif ($sender->is_role === 2) {
            if ($receiver->is_role !== 0 && $receiver->is_role !== 1) {
                return response()->json(['message' => 'Pharmacists can only chat with admins and patients'], 403);
            }
        }
        // Patients can chat with admin and pharmacists
        elseif ($sender->is_role === 1) {
            if ($receiver->is_role !== 0 && $receiver->is_role !== 2) {
                return response()->json(['message' => 'Patients can only chat with admins and pharmacists'], 403);
            }
        }

        $filePath = null;
        $fileType = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $result = $this->cloudinaryService->uploadImage($file, 'chat_files');
            $filePath = $result['secure_url'];
            $fileType = $this->getFileType($file->getMimeType());
            $fileName = $file->getClientOriginalName();
        }

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_name' => $fileName,
        ]);

        broadcast(new \App\Events\ChatMessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    }

    public function getMessages($userId)
    {
        $receiver = User::findOrFail($userId);
        $sender = Auth::user();

        // Check if the conversation is allowed based on roles
        if ($sender->is_role === 0) {
            // Admin can chat with anyone
        } elseif ($sender->is_role === 2) {
            if ($receiver->is_role !== 0 && $receiver->is_role !== 1) {
                return response()->json(['message' => 'Pharmacists can only chat with admins and patients'], 403);
            }
        } elseif ($sender->is_role === 1) {
            if ($receiver->is_role !== 0 && $receiver->is_role !== 2) {
                return response()->json(['message' => 'Patients can only chat with admins and pharmacists'], 403);
            }
        }

        $messages = ChatMessage::where(function($query) use ($userId) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', Auth::id());
        })->with(['sender', 'receiver'])
          ->orderBy('created_at', 'asc')
          ->get();

        return response()->json($messages);
    }

    public function markAsRead($messageId)
    {
        $message = ChatMessage::where('receiver_id', Auth::id())
            ->where('id', $messageId)
            ->firstOrFail();

        $message->markAsRead();

        return response()->json(['message' => 'Message marked as read']);
    }

    public function getConversations()
    {
        $user = Auth::user();
        $conversations = [];

        if ($user->is_role === 0) { // Admin
            $conversations = User::where('id', '!=', $user->id)
                ->whereIn('is_role', [1, 2]) // Get all patients and pharmacists
                ->get();
        } elseif ($user->is_role === 2) { // Pharmacist
            $conversations = User::where('id', '!=', $user->id)
                ->whereIn('is_role', [0, 1]) // Get admin and patients
                ->get();
        } elseif ($user->is_role === 1) { // Patient
            $conversations = User::where('id', '!=', $user->id)
                ->whereIn('is_role', [0, 2]) // Get admin and pharmacists
                ->get();
        }

        return response()->json($conversations);
    }

    private function getFileType($mimeType)
    {
        if (str_contains($mimeType, 'image')) {
            return 'image';
        } elseif (str_contains($mimeType, 'video')) {
            return 'video';
        } else {
            return 'document';
        }
    }
} 