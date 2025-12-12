<?php

use PP\Request;

if (empty($_ENV['OPENAI_API_KEY']) && empty(getenv('OPENAI_API_KEY'))) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'OPENAI_API_KEY is not set']);
    exit;
}

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) {
    @ob_end_flush();
}
@ob_implicit_flush(true);
@set_time_limit(0);

// $raw = file_get_contents('php://input') ?: '';
// $payload = json_decode($raw, true);

$payload = Request::$data ?? null;

$messages = $payload['messages'] ?? null;
if (!is_array($messages) || $messages === []) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Missing body: { "messages": [...] }']) . "\n\n";
    flush();
    exit;
}

$client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY'));

try {
    echo "event: start\n";
    echo "data: {}\n\n";
    flush();

    $stream = $client->chat()->createStreamed([
        'model' => 'gpt-4o',
        'messages' => $messages,
    ]);

    foreach ($stream as $chunk) {
        // Works with openai-php/client streamed chunks
        $choice = $chunk->choices[0]->toArray();
        $deltaText = $choice['delta']['content'] ?? '';

        if ($deltaText !== '') {
            echo "event: token\n";
            echo "data: " . json_encode(['text' => $deltaText]) . "\n\n";
            flush();
        }
    }

    echo "event: done\n";
    echo "data: [DONE]\n\n";
    flush();
} catch (Throwable $e) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => $e->getMessage()]) . "\n\n";
    flush();
}
