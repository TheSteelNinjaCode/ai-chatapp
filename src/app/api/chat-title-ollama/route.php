<?php

use PP\Request;

// 1. Get Input Data
$payload = Request::$data ?? null;
$userContent = $payload['content'] ?? null;

if (empty($userContent)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing "content" field.']);
    exit;
}

// 2. Configuration
$ollamaHost = $_ENV['OLLAMA_HOST'] ?? getenv('OLLAMA_HOST');
if (empty($ollamaHost)) {
    $ollamaHost = 'http://localhost:11434';
}

$ollamaModel = 'qwen3:8b';

try {
    // 3. Prepare Payload
    // Reasoning models need a clear goal to stop thinking quickly.
    $systemInstruction = "Task: Generate a 3-5 word title. Rules: Output ONLY the title. Do not output thoughts or quotes.";

    $messages = [
        ['role' => 'system', 'content' => $systemInstruction],
        ['role' => 'user', 'content' => "Generate title for: \n" . substr($userContent, 0, 500)]
    ];

    $requestData = [
        'model' => $ollamaModel,
        'messages' => $messages,
        'stream' => false,
        'options' => [
            'temperature' => 0.3,
            // FIX: Increased from 200 to 1024. 
            // Reasoning models consume many tokens for "thinking" before generating the "content".
            'num_predict' => 1024
        ]
    ];

    // 4. Execute Request
    $ch = curl_init("$ollamaHost/api/chat");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Increased timeout to accommodate longer generation time
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $rawResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($rawResponse === false) {
        throw new Exception("cURL Failed: " . curl_error($ch));
    }
    curl_close($ch);

    $responseData = json_decode($rawResponse, true);

    // 5. Extract Content
    $fullContent = "";
    $thinkingContent = "";

    if (isset($responseData['message'])) {
        // Standard answer field
        $fullContent = $responseData['message']['content'] ?? "";

        // Some custom models/forks separate thinking into a specific field
        if (isset($responseData['message']['thinking'])) {
            $thinkingContent = $responseData['message']['thinking'];
        }
    }

    // EDGE CASE FIX:
    // If 'content' is empty but we have 'thinking' (and we ran out of tokens),
    // try to salvage a title from the thoughts or default.
    if (empty($fullContent) && !empty($thinkingContent)) {
        // Since the model didn't finish, we fallback to a safe default
        // rather than outputting raw thoughts which are messy.
        $fullContent = "New Conversation";
    }

    // 6. Clean the Output
    // A. Remove <think> tags (if they are embedded in content)
    $cleanTitle = preg_replace('/<think>.*?<\/think>/s', '', $fullContent);

    // B. Remove Markdown and Quotes
    $cleanTitle = str_replace(['*', '"', "'", '`', '#'], '', $cleanTitle);

    // C. Remove Prefixes (Title:, etc)
    $cleanTitle = preg_replace('/^(Title:|Subject:|Here is a title:)\s*/i', '', $cleanTitle);

    // D. Trim
    $cleanTitle = trim($cleanTitle);

    // Final Fallback
    if (empty($cleanTitle)) {
        $cleanTitle = "New Conversation";
    }

    // 7. Return Response
    echo json_encode([
        'success' => true,
        'title' => $cleanTitle
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
