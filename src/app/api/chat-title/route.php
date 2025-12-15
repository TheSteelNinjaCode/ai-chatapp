<?php

use PP\Request;

// 1. Basic Security & Setup
if (empty($_ENV['OPENAI_API_KEY']) && empty(getenv('OPENAI_API_KEY'))) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'OPENAI_API_KEY is not set']);
    exit;
}

// 2. Get Input Data
$payload = Request::$data ?? null;
$userContent = $payload['content'] ?? null;

// Validate input
if (empty($userContent)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing "content" field.']);
    exit;
}

$client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY'));

try {
    // 3. Request Title from AI (Non-streamed)
    // We use 'gpt-4o-mini' because it is cheaper and faster for simple tasks like summarization.
    $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant. Generate a short, concise title (maximum 5 words) for a chat conversation based on the following user message. Do not use quotes, punctuation, or "Title:". Just the text.'
            ],
            [
                'role' => 'user',
                'content' => $userContent
            ]
        ],
        'temperature' => 0.5, // Lower temperature for more deterministic/focused titles
        'max_tokens' => 15,
    ]);

    $title = trim($response->choices[0]->message->content);

    // 4. Return the Title
    echo json_encode([
        'success' => true,
        'title' => $title
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
