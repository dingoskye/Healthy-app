<?php
// api/chat.php — server proxy naar OpenAI; gebruikt .env OPENAI_API_KEY
header('Content-Type: application/json');

// Session & DB
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/database.php';
if (function_exists('mysqli_set_charset')) { mysqli_set_charset($db, 'utf8mb4'); }

// .env helper
function env_get($key, $default=null){
    $v = getenv($key);
    if($v!==false) return $v;
    $path = __DIR__ . '/../.env';
    if (is_file($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line,'=') === false) continue;
            [$k,$val] = array_map('trim', explode('=', $line, 2));
            if ($k === $key) return trim($val, "\"'");
        }
    }
    return $default;
}

// ==== Input ====
$input    = json_decode(file_get_contents('php://input'), true) ?: [];
$model    = $input['model'] ?? 'gpt-4o-mini';
$messages = $input['messages'] ?? [];

// ==== User resolving ====
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;


// ❗ Zonder fallback: als er geen user_id is, geef duidelijke fout terug
if (!$userId) {
    http_response_code(401);
    echo json_encode([
        'error'         => 'Geen ingelogde gebruiker (user_id ontbreekt in sessie).',
        '__session_uid' => null
    ]);
    exit;
}

// ==== Preferences ====
$profileContext = '';
$q = mysqli_query($db, "SELECT preferences FROM users WHERE id = ".(int)$userId." LIMIT 1");
if ($q && $row = mysqli_fetch_assoc($q)) {
    $prefs = trim((string)($row['preferences'] ?? ''));
    if ($prefs !== '') {
        $profileContext =
            'De gebruiker heeft de volgende eetvoorkeuren doorgegeven: "'
            . $prefs . '". Houd hier ALTIJD rekening mee in je adviezen.';
    }
}
if ($profileContext !== '') {
    array_unshift($messages, ['role'=>'system','content'=>$profileContext]);
}

// ==== Meals (laatste 7 dagen) ====
$mealsContext = '';
$q2 = mysqli_query(
    $db,
    "SELECT eaten_at, meal_type, protein_g, carbs_g, fat_g, fiber_g, notes, dish
     FROM meals
     WHERE user_id = ".(int)$userId."
       AND eaten_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     ORDER BY eaten_at DESC
     LIMIT 200"
);
if ($q2) {
    $lines = [];
    while ($r = mysqli_fetch_assoc($q2)) {
        $parts = [];
        if (!empty($r['eaten_at'])) $parts[] = 'eaten_at=' . date('Y-m-d H:i', strtotime($r['eaten_at']));
        if (!empty($r['meal_type'])) $parts[] = 'meal_type=' . $r['meal_type'];
        if (!empty($r['dish']))      $parts[] = 'dish=' . $r['dish'];
        foreach (['protein_g','carbs_g','fat_g','fiber_g'] as $k) {
            if ($r[$k] !== null && $r[$k] !== '') $parts[] = "$k={$r[$k]}";
        }
        if (!empty($r['notes'])) $parts[] = 'notes=' . $r['notes'];
        if ($parts) $lines[] = '- ' . implode(', ', $parts);
    }
    if ($lines) {
        $mealsContext =
            "Maaltijdgeschiedenis van de gebruiker (laatste 7 dagen, nieuwste eerst):\n"
            . implode("\n", $lines)
            . "\n\nKijk vooral naar wat de gebruiker de laatste week heeft gegeten en baseer je advies daarop.";
    }
}
// ✅ Plaats dit in $messages (je had hier $mealHistory staan)
if ($mealsContext !== '') {
    array_unshift($messages, ['role'=>'system','content'=>$mealsContext]);
}

// ==== OpenAI ====
$apiKey = env_get('OPENAI_API_KEY');
if (!$apiKey) { http_response_code(500); echo json_encode(['error'=>'OPENAI_API_KEY ontbreekt']); exit; }

$body = [
    'model'       => $model,
    'temperature' => 0.6,
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

if ($err) { http_response_code(500); echo json_encode(['error'=>'Request error','detail'=>$err]); exit; }

$data  = json_decode($res, true);
$reply = $data['choices'][0]['message']['content'] ?? '(No response)';

echo json_encode([
    'reply'         => $reply,
    '__session_uid' => $userId,
    '__prefs_ctx'   => $profileContext,
    '__meals_ctx'   => $mealsContext,
]);
