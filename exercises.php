<?php
session_start();
/** @var mysqli $db */
require_once 'includes/database.php';

// Get user ID (from session or default)
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch preferences
$prefs = [];
$stmt = $db->prepare("SELECT * FROM `exercise_settings` WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_prefs = false;
if ($result) {
    $prefs = $result->fetch_assoc() ?: [];
    $has_prefs = !empty($prefs); // True if row exists
}
$stmt->close();

// Set variables for form
$goal = $prefs['goal'] ?? '';
$level = $prefs['level'] ?? '';
$equipment = $prefs['equipment'] ?? '';
$time_limit = $prefs['time_limit'] ?? '';
$focus_area = $prefs['focus_area'] ?? '';

// Save preferences if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $goal = $_POST['goal'];
    $level = $_POST['level'];
    $equipment = $_POST['equipment'];
    $time_limit = $_POST['time_limit'];
    $focus_area = $_POST['focus_area'];

    $stmt = $db->prepare("REPLACE INTO `exercise_settings` (user_id, goal, level, equipment, time_limit, focus_area) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssis", $user_id, $goal, $level, $equipment, $time_limit, $focus_area);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid resubmission and reload exercises
    header("Location: exercises.php");
    exit;
}

// Generate exercises
// Generate exercises
function generateExercises($goal, $level, $equipment, $time_limit, $focus_area) {
    $prompt = "Generate a $time_limit minute $level workout for $goal, focusing on $focus_area, using $equipment. Give a list of exercises with sets and reps.";
    $messages = [
            ['role' => 'system', 'content' => 'You are a helpful fitness coach.'],
            ['role' => 'user', 'content' => $prompt]
    ];

    $data = [
            'model' => 'gpt-4o-mini',
            'messages' => $messages
    ];

    // ✅ Bouw dynamisch de URL naar api/chat.php
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host   = $_SERVER['HTTP_HOST'];
    $path   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $url    = $scheme . "://" . $host . $path . "/api/exercisesgenerate.php";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);

    // Debug + parsing
    if ($err) {
        return 'Curl error: ' . $err;
    }
    if (!$res) {
        return '(Empty response)';
    }

    $json = json_decode($res, true);
    if (isset($json['reply'])) {
        return $json['reply'];
    } elseif (isset($json['error'])) {
        return 'Error: ' . $json['error'] . (isset($json['detail']) ? ' (' . $json['detail'] . ')' : '');
    }
    return 'Unexpected response: ' . $res;
}

// Only generate exercises if preferences exist
$exercises = '';
if ($has_prefs) {
    $exercises = generateExercises($goal, $level, $equipment, $time_limit, $focus_area);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Exercises</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[var(--background)] min-h-screen flex flex-col text-gray-800">
<nav class="flex items-center justify-between px-6 py-4 bg-white shadow">
    <span class="font-bold text-lg">
        <a href="index.php">Nutricoach</a>
    </span>
    <div class="space-x-4 text-xl">
        <a href="#" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>
<header class="text-center mt-12">
    <h1>Fitness exercises</h1>
    <p>These exercises are based on age, skill level
        and personal preferences</p>
</header>
<main class="text-center mt-12">
    <section>
        <div class="bg-base-200 shadow rounded-lg my-4">
            <button type="button" id="toggleForm" class="w-full text-left text-lg font-medium px-4 py-3 focus:outline-none">
                Personal preferences
            </button>
            <div id="prefsForm" class="collapse-content" style="display: none;">
                <form method="POST" action="">
                    <!-- Goal -->
                    <div class="mb-4">
                        <label for="goal" class="block text-sm font-medium mb-1">Goal</label>
                        <select id="goal" name="goal" class="select select-bordered w-full text-center" required>
                            <option value="muscle_gain" <?= $goal === 'muscle_gain' ? 'selected' : '' ?>>Muscle gain</option>
                            <option value="weight_loss" <?= $goal === 'weight_loss' ? 'selected' : '' ?>>Weight loss</option>
                            <option value="condition" <?= $goal === 'condition' ? 'selected' : '' ?>>Condition improvement</option>
                            <option value="balance" <?= $goal === 'balance' ? 'selected' : '' ?>>Balance and flexibility</option>
                        </select>
                    </div>
                    <!-- level -->
                    <div class="mb-4">
                        <label for="level" class="block text-sm font-medium mb-1">Level</label>
                        <select id="level" name="level" class="select select-bordered w-full text-center" required>
                            <option value="beginner" <?= $level === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $level === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        </select>
                    </div>
                    <!-- equipment -->
                    <div class="mb-4">
                        <label for="equipment" class="block text-sm font-medium mb-1">equipment</label>
                        <input type="text" id="equipment" name="equipment" value="<?= htmlspecialchars($equipment) ?>" class="input input-bordered w-full text-center" placeholder="Say dumbbells, resistance band, none" required>
                    </div>
                    <!-- Time limit -->
                    <div class="mb-4">
                        <label for="time_limit" class="block text-sm font-medium mb-1">Time limit (in minutes)</label>
                        <input type="number" id="time_limit" name="time_limit" value="<?= htmlspecialchars($time_limit) ?>" class="input input-bordered w-full text-center" required>
                    </div>
                    <!-- Focus area -->
                    <div class="mb-4">
                        <label for="focus_area" class="block text-sm font-medium mb-1">Focus area</label>
                        <select id="focus_area" name="focus_area" class="select select-bordered w-full text-center" required>
                            <option value="full_body" <?= $focus_area === 'full_body' ? 'selected' : '' ?>>Full body</option>
                            <option value="arms" <?= $focus_area === 'arms' ? 'selected' : '' ?>>Arms</option>
                            <option value="legs" <?= $focus_area === 'legs' ? 'selected' : '' ?>>Legs</option>
                            <option value="core" <?= $focus_area === 'core' ? 'selected' : '' ?>>Core (stomach / back)</option>
                            <option value="cardio" <?= $focus_area === 'cardio' ? 'selected' : '' ?>>Cardio</option>
                        </select>
                    </div>

                    <button type="submit" name="save_settings" class="btn btn-primary">Save settings</button>
                </form>
            </div>
        </div>
        <script src="src/JS/collapsible.js"></script>
    </section>
    <section>
        <div>
            <p id="current-date"></p>
            <script src="src/JS/date.js"></script>
        </div>
        <?php if (!empty($exercises)): ?>
            <div class="bg-white rounded-lg shadow p-6 my-6">
                <h2 class="text-xl font-semibold mb-2">Your personalized workout</h2>
                <div class="prose max-w-none whitespace-pre-line"><?= nl2br(htmlspecialchars($exercises)) ?></div>
            </div>
        <?php endif; ?>
    </section>

    <div>
        <p>Hier komt de chatbot</p>
    </div>
</main>
<footer class="py-6 text-center text-gray-300 text-sm bg-[var(--header-nav)]">
    <p>©Nutricoach</p>
</footer>
</body>
</html>
