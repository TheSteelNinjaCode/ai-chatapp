<?php

use PP\Request;
use Lib\MCP\CareerTools;

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

$payload = Request::$data ?? null;
$messages = $payload['messages'] ?? null;

if (!is_array($messages) || $messages === []) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Missing body: { "messages": [...] }']) . "\n\n";
    flush();
    exit;
}

$client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY'));

// =================================================================
// 1. SETUP TOOLS
// =================================================================

$careerTools = new CareerTools();

$availableTools = [
    'search-careers'  => fn($args) => $careerTools->searchCareers($args['query'] ?? ''),
    'get-all-careers' => fn($args) => $careerTools->getAllCareers(),
];

$toolsSchema = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'search-careers',
            'description' => 'Search the university database for specific careers.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Keywords (e.g. Engineering)']
                ],
                'required' => ['query']
            ]
        ]
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'get-all-careers',
            'description' => 'Get a full list of all available careers and degrees.',
            'parameters' => [
                'type' => 'object',
                'properties' => (object)[], // Force JSON object
                'required' => []
            ]
        ]
    ]
];

// =================================================================
// 2. UPDATED SYSTEM PROMPT (THE FIX)
// =================================================================

$systemInstruction = [
    "role" => "system",
    "content" => "IMPORTANT: You are an academic advisor for this specific university. 
    1. WHEN asked about study options, careers, or recommendations, you MUST FIRST use the `search-careers` tool to see what is actually available in the database.
    2. DO NOT hallucinate careers that are not returned by the tool.
    3. IF the tool returns results, base your answer strictly on those results.
    4. IF the tool returns 'No results', explicitly state that the university does not offer that specific program, and ONLY THEN provide general advice or alternatives."
];

// Only add system instruction if it's not already there
if (($messages[0]['role'] ?? '') !== 'system') {
    array_unshift($messages, $systemInstruction);
}

try {
    echo "event: start\n";
    echo "data: {}\n\n";
    flush();

    $stream = $client->chat()->createStreamed([
        'model'       => 'gpt-4o',
        'messages'    => $messages,
        'tools'       => $toolsSchema,
        'tool_choice' => 'auto',
    ]);

    $toolCalls = [];
    $isToolCall = false;

    foreach ($stream as $chunk) {
        $choice = $chunk->choices[0]->toArray();
        $delta  = $choice['delta'];

        if (isset($delta['tool_calls'])) {
            $isToolCall = true;
            foreach ($delta['tool_calls'] as $tc) {
                $idx = $tc['index'];
                if (!isset($toolCalls[$idx])) {
                    $toolCalls[$idx] = [
                        'id'       => $tc['id'] ?? null,
                        'type'     => 'function',
                        'function' => ['name' => $tc['function']['name'] ?? '', 'arguments' => '']
                    ];
                }
                if (isset($tc['function']['arguments'])) {
                    $toolCalls[$idx]['function']['arguments'] .= $tc['function']['arguments'];
                }
            }
        }

        if (!$isToolCall && isset($delta['content']) && $delta['content'] !== '') {
            echo "event: token\n";
            echo "data: " . json_encode(['text' => $delta['content']]) . "\n\n";
            flush();
        }
    }

    if (!empty($toolCalls)) {
        $messages[] = [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => array_values($toolCalls)
        ];

        foreach ($toolCalls as $toolCall) {
            $name = $toolCall['function']['name'];
            $args = json_decode($toolCall['function']['arguments'], true);
            $id   = $toolCall['id'];

            $result = "Tool error";
            if (isset($availableTools[$name])) {
                try {
                    $result = $availableTools[$name]($args);
                } catch (Throwable $e) {
                    $result = "System Error: " . $e->getMessage();
                }
            }

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $id,
                'content' => (string) $result
            ];
        }

        $secondStream = $client->chat()->createStreamed([
            'model'    => 'gpt-4o',
            'messages' => $messages,
        ]);

        foreach ($secondStream as $chunk) {
            $deltaText = $chunk->choices[0]->delta->content ?? '';
            if ($deltaText !== '') {
                echo "event: token\n";
                echo "data: " . json_encode(['text' => $deltaText]) . "\n\n";
                flush();
            }
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
