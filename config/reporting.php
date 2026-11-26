<?php

return [
    'ai' => [
        'key' => env('REPORT_AI_KEY'),
        'model' => env('REPORT_AI_MODEL'),
        'endpoint' => env('REPORT_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
    ],
];
