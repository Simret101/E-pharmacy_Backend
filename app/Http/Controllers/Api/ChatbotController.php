<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Models\ChatHistory;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Get drug information from chatbot
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrugInfo(Request $request)
    {
        try {
            $query = $request->input('query');
            $response = $this->chatbotService->getDrugInfo($query);
            
            // Save chat history
            ChatHistory::create([
                'user_id' => auth()->id(),
                'query' => $query,
                'response' => $response['response'],
                'drug_name' => $response['drug_name'],
                'similarity_score' => $response['similarity_score'],
                'is_pregnancy_query' => $response['is_pregnancy_query']
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's chat history
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserChatHistory(Request $request)
    {
        $history = ChatHistory::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($history);
    }

    /**
     * Get chat history for a specific drug
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrugChatHistory(Request $request)
    {
        $drugName = $request->input('drug_name');
        $history = ChatHistory::where('user_id', auth()->id())
            ->where('drug_name', $drugName)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($history);
    }

    /**
     * Get chatbot API health status
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkHealth()
    {
        try {
            $health = $this->chatbotService->checkHealth();
            return response()->json($health);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
