<?php

return [
    'url' => env('CHATBOT_API_URL', 'https://rag-based-ai-chatbot.onrender.com'),
    'timeout' => 30, // seconds
    'retry_attempts' => 3,
    'retry_delay' => 1000, // milliseconds
];
