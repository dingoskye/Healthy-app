<?php
// api/chat.php — server proxy naar OpenAI; gebruikt .env OPENAI_API_KEY
header('Content-Type: application/json');

function env_get($key, $default=null){
    $v = getenv($key);
    if($v!==false) return $v;
    $path = __DIR__ . '/../.env';
    if (is_file($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (!str_contains($line,'=')) continue;
            [$k,$val] = array_map('trim', explode('=', $line, 2));
            if ($k === $key) return trim($val, "\"'");
        }
    }
    return $default;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$model = $input['model'] ?? 'gpt-4o-mini';
$messages = $input['messages'] ?? [];

$apiKey = env_get('OPENAI_API_KEY');
if (!$apiKey) { http_response_code(500); echo json_encode(['error'=>'OPENAI_API_KEY ontbreekt']); exit; }

$body = [
    'model' => $model,
    'temperature' => 0.7,
    'messages' => $messages,
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_TIMEOUT => 30,
]);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) { http_response_code(500); echo json_encode(['error'=>'Request error','detail'=>$err]); exit; }
$data = json_decode($res, true);
$reply = $data['choices'][0]['message']['content'] ?? '(No response)';

echo json_encode(['reply' => $reply]);
