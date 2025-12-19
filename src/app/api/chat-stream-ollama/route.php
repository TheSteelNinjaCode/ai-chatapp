<?php

use PP\Request;

// 1. CONFIGURATION
// Ensure this matches your "ollama list" output exactly
$ollamaHost = 'http://localhost:11434';
$modelName = 'deepseek-r1:8b';

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// 2. DISABLE BUFFERING
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) {
    @ob_end_flush();
}
@ob_implicit_flush(true);
@set_time_limit(0);

// 3. GET INPUT
// Assuming PP\Request handles parsing the JSON body
$payload = Request::$data ?? null;
$messages = $payload['messages'] ?? null;

if (!is_array($messages) || $messages === []) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Missing body: { "messages": [...] }']) . "\n\n";
    flush();
    exit;
}

// 4. SEND START EVENT
echo "event: start\n";
echo "data: {}\n\n";
flush();

// 5. INITIALIZE CURL
$ch = curl_init();

$postData = [
    'model' => $modelName,
    'messages' => $messages,
    'stream' => true
];

curl_setopt_array($ch, [
    CURLOPT_URL => "$ollamaHost/api/chat",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 300, // 5 minute timeout for long thoughts

    // STREAMING CALLBACK
    CURLOPT_WRITEFUNCTION => function ($ch, $data) {
        // Ollama might send multiple JSON objects in one chunk
        $lines = explode("\n", $data);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $json = json_decode($line, true);

            if (!is_array($json)) continue;

            // Handle API Errors (e.g., model loading failed)
            if (isset($json['error'])) {
                echo "event: error\n";
                echo "data: " . json_encode(['message' => 'Ollama Error: ' . $json['error']]) . "\n\n";
                flush();
                continue;
            }

            // Handle Content
            if (isset($json['message']['content'])) {
                $content = $json['message']['content'];

                // Only send non-empty strings
                if ($content !== '') {
                    echo "event: token\n";
                    echo "data: " . json_encode(['text' => $content]) . "\n\n";
                    flush();
                }
            }
        }

        return strlen($data);
    }
]);

// 6. EXECUTE
$result = curl_exec($ch);

// 7. CHECK FOR SYSTEM/NETWORK ERRORS
if (curl_errno($ch)) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Curl error: ' . curl_error($ch)]) . "\n\n";
    flush();
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        echo "event: error\n";
        echo "data: " . json_encode(['message' => "HTTP Error $httpCode from Ollama"]) . "\n\n";
        flush();
    }
}

// 8. SEND DONE EVENT
echo "event: done\n";
echo "data: [DONE]\n\n";
flush();
