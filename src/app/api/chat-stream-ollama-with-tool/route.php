<?php

use PP\Request;
use Lib\MCP\CareerTools;

// =================================================================
// 1. CONFIGURATION
// =================================================================

$modelName = 'qwen3:8b';
$ollamaUrl = 'http://localhost:11434/v1/chat/completions';

// =================================================================
// 2. SERVER HEADERS & ENVIRONMENT SETUP
// =================================================================

// Prevent timeouts during long AI generation
@set_time_limit(0);
@ini_set('max_execution_time', '0');

// Headers for Server-Sent Events (SSE)
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// Disable Output Buffering
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) @ob_end_flush();
@ob_implicit_flush(true);

// =================================================================
// 3. INITIALIZE TOOLS
// =================================================================

try {
    $careerTools = new CareerTools();
} catch (Throwable $e) {
    sendError("Tool Initialization Failed: " . $e->getMessage());
    exit;
}

$availableTools = [
    'search-careers'  => fn($args) => $careerTools->searchCareers($args['query'] ?? ''),
    'get-all-careers' => fn($args) => $careerTools->getAllCareers(),
];

$toolsSchema = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'search-careers',
            'description' => 'Search for specific careers by keyword.',
            'parameters' => [
                'type' => 'object',
                'properties' => ['query' => ['type' => 'string']],
                'required' => ['query']
            ]
        ]
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'get-all-careers',
            'description' => 'Get a full list and count of all available careers.',
            'parameters' => [
                'type' => 'object',
                'properties' => (object)[],
                'required' => []
            ]
        ]
    ]
];

// =================================================================
// 4. INPUT PROCESSING & SYSTEM PROMPT
// =================================================================

$payload = Request::$data ?? null;
$messages = $payload['messages'] ?? null;

if (empty($messages) || !is_array($messages)) {
    sendError('Missing body: { "messages": [...] }');
    exit;
}

// Mandatory Rules for Academic Advisor Persona
$toolRules = "
IMPORTANT RULES FOR ACADEMIC ADVISOR:
1. If the user asks for a specific career, YOU MUST use `search-careers`.
2. If the user asks to LIST ALL careers (e.g. 'listar todas', 'show all'), YOU MUST use `get-all-careers`.
3. Do not answer from your own knowledge. USE THE TOOLS.
";

// Inject Rules into System Prompt
$foundSystem = false;
foreach ($messages as &$msg) {
    if ($msg['role'] === 'system') {
        $msg['content'] .= "\n\n" . $toolRules;
        $foundSystem = true;
        break;
    }
}
unset($msg);

if (!$foundSystem) {
    array_unshift($messages, ["role" => "system", "content" => $toolRules]);
}

// Send Start Event
echo "event: start\n";
echo "data: {}\n\n";
flush();

// =================================================================
// 5. CORE FUNCTIONS
// =================================================================

/**
 * Encodes data to JSON safely, handling invalid UTF-8 characters.
 */
function safe_json_encode($data)
{
    $encoded = json_encode($data, JSON_INVALID_UTF8_IGNORE);
    if ($encoded === false) {
        return json_encode(["error" => "Encoding Failed: " . json_last_error_msg()]);
    }
    return $encoded;
}

/**
 * Streams response from Ollama and captures tool calls.
 */
function streamOllama($url, $model, $messages, $tools)
{
    $ch = curl_init();

    $postData = [
        'model' => $model,
        'messages' => $messages,
        'stream' => true,
        'tools' => $tools,
        'tool_choice' => 'auto'
    ];

    $jsonPayload = safe_json_encode($postData);

    $toolCalls = [];
    $buffer = "";

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 300, // 5 minutes
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],

        CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$toolCalls, &$buffer) {
            $buffer .= $chunk;

            while (($newlinePos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $newlinePos);
                $buffer = substr($buffer, $newlinePos + 1);
                $line = trim($line);

                if (empty($line)) continue;

                // Stop immediately on [DONE] to prevent timeout loop
                if (strpos($line, '[DONE]') !== false) return -1;

                if (strpos($line, 'data:') !== 0) continue;

                $jsonStr = substr($line, 5);
                $json = json_decode($jsonStr, true);

                if (!isset($json['choices'][0]['delta'])) continue;
                $delta = $json['choices'][0]['delta'];

                // 1. Buffer Tool Calls
                if (isset($delta['tool_calls'])) {
                    foreach ($delta['tool_calls'] as $tc) {
                        $idx = $tc['index'];
                        if (!isset($toolCalls[$idx])) {
                            $toolCalls[$idx] = [
                                'id' => $tc['id'] ?? uniqid('call_'),
                                'type' => 'function',
                                'function' => ['name' => '', 'arguments' => '']
                            ];
                        }
                        if (isset($tc['function']['name'])) $toolCalls[$idx]['function']['name'] .= $tc['function']['name'];
                        if (isset($tc['function']['arguments'])) $toolCalls[$idx]['function']['arguments'] .= $tc['function']['arguments'];
                    }
                }

                // 2. Stream Text Content
                if (isset($delta['content'])) {
                    $content = (string)$delta['content'];
                    if ($content !== '') {
                        sendToken($content);
                    }
                }
            }
            return strlen($chunk);
        }
    ]);

    curl_exec($ch);

    // Check for connection errors (Ignoring manual abort codes 23/42)
    if (curl_errno($ch) && curl_errno($ch) !== 23 && curl_errno($ch) !== 42) {
        sendError("Connection Error: " . curl_error($ch));
    }

    return $toolCalls;
}

function sendToken($text)
{
    echo "event: token\n";
    echo "data: " . json_encode(['text' => $text]) . "\n\n";
    flush();
}

function sendError($message)
{
    echo "event: error\n";
    echo "data: " . json_encode(['message' => $message]) . "\n\n";
    flush();
}

// =================================================================
// 6. MAIN EXECUTION FLOW
// =================================================================

try {
    // Step 1: Initial Request
    $toolCalls = streamOllama($ollamaUrl, $modelName, $messages, $toolsSchema);

    // Step 2: Handle Tools (if triggered)
    if (!empty($toolCalls)) {

        // Add Assistant Intent to History
        $messages[] = [
            'role' => 'assistant',
            'content' => "", // Empty string required for Ollama context
            'tool_calls' => array_values($toolCalls)
        ];

        // Execute Tools
        foreach ($toolCalls as $call) {
            $name = $call['function']['name'];
            $argsStr = $call['function']['arguments'];
            $callId = $call['id'];

            $output = "Error: Tool not found";

            if (isset($availableTools[$name])) {
                try {
                    $args = json_decode($argsStr, true);
                    $output = $availableTools[$name]($args ?? []);

                    // Sanitize Output to ensure UTF-8 validity
                    if (!mb_check_encoding($output, 'UTF-8')) {
                        $output = mb_convert_encoding($output, 'UTF-8', 'UTF-8');
                    }
                } catch (Throwable $e) {
                    $output = "Tool Execution Error: " . $e->getMessage();
                }
            }

            // Add Tool Result to History
            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $callId,
                'content' => (string)$output
            ];
        }

        // Step 3: Final Answer (Recursive call with updated history)
        streamOllama($ollamaUrl, $modelName, $messages, $toolsSchema);
    }

    echo "event: done\n";
    echo "data: [DONE]\n\n";
    flush();
} catch (Throwable $e) {
    sendError("System Error: " . $e->getMessage());
}
