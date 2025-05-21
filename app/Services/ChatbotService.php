<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatbotService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://rag-based-ai-chatbot.onrender.com';
    }

    /**
     * Get drug information from the chatbot API
     * @param string $query
     * @return array
     */
    public function getDrugInfo(string $query)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/chat', [
                'query' => $query
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            $error = $response->json() ?? ['error' => true, 'message' => $response->body()];
            throw new \Exception('Chatbot API error: ' . $error['message']);
        } catch (\Exception $e) {
            \Log::error('Chatbot API Error: ' . $e->getMessage());
            throw new \Exception('Failed to connect to chatbot API. Please check the API URL and try again.');
        }
    }

    /**
     * Check API health
     * @return array
     */
    public function checkHealth()
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . '/health');
            
            if ($response->successful()) {
                return $response->json();
            }

            $error = $response->json() ?? ['error' => true, 'message' => $response->body()];
            throw new \Exception('Health check failed: ' . $error['message']);
        } catch (\Exception $e) {
            \Log::error('Chatbot API Health Check Error: ' . $e->getMessage());
            throw new \Exception('Failed to check chatbot API health. Please verify the API is running.');
        }
    }
}
