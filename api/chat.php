<?php
// api/chat.php — server proxy naar OpenAI; gebruikt .env OPENAI_API_KEY
header('Content-Type: application/json');

// Session & DB
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/database.php';

// (aanrader) juiste charset
if (function_exists('mysqli_set_charset')) {
    mysqli_set_charset($db, 'utf8mb4');
}

// .env helper
function env_get($key, $default=null){
    $v = getenv($key);
    if($v!==false) return $v;
    $path = __DIR__ . '/../.env';
    if (is_file($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line,'=') === false) continue; // compat
            [$k,$val] = array_map('trim', explode('=', $line, 2));
            if ($k === $key) return trim($val, "\"'");
        }
    }
    return $default;
}

// Input
$input    = json_decode(file_get_contents('php://input'), true) ?: [];
$model    = $input['model'] ?? 'gpt-4o-mini';
$messages = $input['messages'] ?? [];

// ============= USER RESOLVING + PREFERENCES =============

// 1) haal user_id uit sessie
$userId = $_SESSION['user_id'] ?? null;

// 2) DEV fallback (alleen voor lokaal testen!): pak laatste user als er geen sessie is
if (!$userId) {
    $resLast = mysqli_query($db, "SELECT id FROM users ORDER BY id DESC LIMIT 1");
    if ($resLast && $rowLast = mysqli_fetch_assoc($resLast)) {
        $userId = (int)$rowLast['id'];
        // TIP: inloggen zet normaal $_SESSION['user_id'] = <id>; fix dat in login.php
    }
}

$profileContext = '';
if ($userId) {
    $uid = (int)$userId;
    $q = mysqli_query($db, "SELECT preferences FROM users WHERE id = $uid LIMIT 1");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        $prefs = trim((string)($row['preferences'] ?? ''));
        if ($prefs !== '') {
            // Geef de preferences exact door als system-context
            $profileContext = 'De gebruiker heeft de volgende eetvoorkeuren doorgegeven: "'
                . $prefs . '". Houd hier ALTIJD rekening mee in je adviezen.';
        }
    }
}

// Voeg de context als eerste system-bericht toe
if ($profileContext !== '') {
    array_unshift($messages, [
        'role'    => 'system',
        'content' => $profileContext,
    ]);
}

// ============= OPENAI CALL =============
$apiKey = env_get('OPENAI_API_KEY');
if (!$apiKey) { http_response_code(500); echo json_encode(['error'=>'OPENAI_API_KEY ontbreekt']); exit; }

$body = [
    'model'       => $model,
    'temperature' => 0.6, // iets gehoorzamer
    'messages'    => $messages,
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

if ($err) {
    http_response_code(500);
    echo json_encode(['error'=>'Request error','detail'=>$err]);
    exit;
}

$data  = json_decode($res, true);
$reply = $data['choices'][0]['message']['content'] ?? '(No response)';

// DEBUG meegeven zodat je in devtools kunt zien wat er gebeurt
echo json_encode([
    'reply'         => $reply,
    '__session_uid' => $userId,
    '__prefs_ctx'   => $profileContext,
]);
