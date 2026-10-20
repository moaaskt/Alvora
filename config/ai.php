<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Groq API Configuration (Chat Ultra Rápido de Baixa Latência)
    |--------------------------------------------------------------------------
    */
    'groq' => [
        'api_key' => env('GROQ_API_KEY', ''),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'endpoint' => env('GROQ_ENDPOINT', 'https://api.groq.com/openai/v1/chat/completions'),
        'timeout' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Configuration (Fallback Resiliente para Geração & Insights)
    |--------------------------------------------------------------------------
    */
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', ''),
        'endpoint' => env('OPENROUTER_ENDPOINT', 'https://openrouter.ai/api/v1/chat/completions'),
        'models_fallback' => [
            'deepseek/deepseek-r1:free',
            'meta-llama/llama-3.3-70b-instruct:free',
            'qwen/qwen3-235b-a22b:free',
        ],
        'timeout' => 8,
    ],
];
