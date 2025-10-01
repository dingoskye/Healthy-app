<?php

// api/exercises.php — proxy naar OpenAI voor fitness oefeningen
header('Content-Type: application/json');

// Session & DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/database.php';

// Zorg voor juiste charset
if (function_exists('mysqli_set_charset')) {
    mysqli_set_charset($db, 'utf8mb4');
}

// .env helper
function env_get($key, $default = null)
{
    $v = getenv($key);
    if ($v !== false) return $v;
    $path = __DIR__ . '/../.env';
    if (is_file($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$k, $val] = array_map('trim', explode('=', $line, 2));
            if ($k === $key) return trim($val, "\"'");
        }
    }
    return $default;
}

// Input ophalen
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$model = $input['model'] ?? 'gpt-4o-mini';
$messages = $input['messages'] ?? [];

// ===== User & Exercise Settings ophalen =====
$userId = $_SESSION['user_id'] ?? null;

$profileContext = '';
if ($userId) {
    $uid = (int)$userId;
    $q = mysqli_query($db, "SELECT * FROM exercise_settings WHERE user_id = $uid LIMIT 1");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        $prefsText = "Goal: {$row['goal']}, Level: {$row['level']}, Equipment: {$row['equipment']}, Time limit: {$row['time_limit']} min, Focus area: {$row['focus_area']}";
        $profileContext = "De gebruiker heeft de volgende fitnessvoorkeuren ingesteld: {$prefsText}. Houd hier rekening mee in je adviezen.";
    }
}

// Voeg system-context toe als die bestaat
if ($profileContext !== '') {
    array_unshift($messages, [
        'role' => 'system',
        'content' => $profileContext,
    ]);
}

// ===== OpenAI API Call =====
$apiKey = env_get('OPENAI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'OPENAI_API_KEY ontbreekt']);
    exit;
}

$body = [
    'model' => $model,
    'temperature' => 0.6,
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

if ($err) {
    http_response_code(500);
    echo json_encode(['error' => 'Request error', 'detail' => $err]);
    exit;
}

$data = json_decode($res, true);
$reply = $data['choices'][0]['message']['content'] ?? '(No response)';

// Resultaat teruggeven
echo json_encode([
    'reply' => $reply,
    '__session_uid' => $userId,
    '__prefs_ctx' => $profileContext,
]);