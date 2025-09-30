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

// Input for the AI context
$input    = json_decode(file_get_contents('php://input'), true) ?: [];
$model    = $input['model'] ?? 'gpt-4o-mini';
$messages = $input['messages'] ?? [];
$mealHistory = $input['mealHistory'] ?? [];

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

// Takes the userId and performs a SQL query based on that userId for preferences
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

//Takes the userId and performs a SQL query based on that userId for meal history
$mealsContext = '';
if ($userId) {
    $uid = (int)$userId;

    // last 7 days, newest first (cap to a reasonable max to save tokens)
    $q = mysqli_query(
        $db,
        "SELECT *
         FROM meals
         WHERE user_id = $uid
           AND eaten_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY eaten_at DESC
         LIMIT 200"
    );

    if ($q) {
        $lines = [];

        // read ALL rows (your original only read one)
        while ($row = mysqli_fetch_assoc($q)) {
            // Build a compact line per meal.
            // Adjust keys if you have specific columns; this generic version works with "*".
            $parts = [];

            // Datetime (format nicely if present)
            if (!empty($row['eaten_at'])) {
                $ts = strtotime($row['eaten_at']);
                $parts[] = 'eaten_at=' . ($ts ? date('Y-m-d H:i', $ts) : $row['eaten_at']);
            }

            // Common fields (only add if present)
            if (!empty($row['meal_type']))   $parts[] = 'meal_type=' . $row['meal_type'];
            if (!empty($row['protein_g']))   $parts[] = 'protein_g=' . $row['protein_g'];
            if (!empty($row['carbs_g']))     $parts[] = 'carbs_g='   . $row['carbs_g'];
            if (!empty($row['fat_g']))       $parts[] = 'fat_g='     . $row['fat_g'];
            if (!empty($row['notes']))       $parts[] = 'notes='     . $row['notes'];

            // Fallback: if you have a 'name' or 'item' column
            if (empty($parts)) {
                // remove noisy keys and dump the rest as key=value
                foreach ($row as $k => $v) {
                    if ($v === null || $v === '' || $k === 'id' || $k === 'user_id') continue;
                    $parts[] = "$k=$v";
                }
            }

            if (!empty($parts)) {
                $lines[] = '- ' . implode(', ', $parts);
            }
        }

        if (!empty($lines)) {
            $mealsContext =
                "Maaltijdgeschiedenis van de gebruiker (laatste 7 dagen, nieuwste eerst):\n" .
                implode("\n", $lines) .
                "\n\nKijk vooral naar wat de gebruiker de laatste week heeft gegeten en baseer je advies daarop. Als er nutrition fields missen, maak een schatting op 1 serving size";
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

if ($mealsContext !== '') {
    array_unshift($mealHistory, [
        'role' => 'system',
        'content' => $mealsContext,
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
    '__mls_ctx'     => $mealsContext,
]);
