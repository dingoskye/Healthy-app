<?php
session_start();
/** @var mysqli $db */
require_once 'includes/database.php';

// Get user ID (from session or default)
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch preferences
$prefs = [];
//$stmt = $db->prepare("SELECT * FROM `exercise_settings` WHERE user_id = ?");
$stmt = $db->prepare("SELECT * FROM `exercise_settings` WHERE user_id = ? ORDER BY id DESC LIMIT 1");
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
function generateExercises($goal, $level, $equipment, $time_limit, $focus_area) {
    $prompt = "Generate a $time_limit minute $level workout for $goal, focusing on $focus_area, using $equipment. Give a list of exercises with sets, reps and explanation.";
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
<body class=" min-h-screen flex flex-col text-gray-800 bg-[var(--text-block)]">
<nav class="bg-[var(--header-nav)] text-white p-4 flex justify-between items-center">
    <span class="font-bold text-lg">
        <a href="index.php"
           class="font-bold text-2xl transition-transform duration-200 hover:scale-110 inline-block">
    Nutricoach
</a>
    </span>
    <div class="space-x-4 flex">
        <!-- Settings button -->
        <a href="#" id="settingsBtn" class="hover:font-bold">
            <img src="src/images/menu.png" alt="dropDownMenu"
                 class="p-1 rounded hover:bg-gray-200 transition-colors duration-200">
        </a>
        <a href="profile.php" class="hover:underline">
            <img src="src/images/user.png" alt="Notifications"
                 class="p-1 rounded hover:bg-gray-200 transition-colors duration-200">
        </a>
    </div>
</nav>


<header class="text-center mt-12">
    <h1 class="text-xl font-semibold mb-2">Fitness exercises</h1>
    <p>These exercises are based on age, skill level
        and personal preferences. <br> The personal preferences can be changed with the button below. <br> <br> Note to first time users, you'll need to set your preferences to generate the workout.</p>
</header>
<main class="flex-grow text-center mt-12">
    <section class="max-w-xl mx-auto">
        <div class="bg-base-200 shadow rounded-lg my-4">
            <button type="button" id="toggleForm" class="w-full text-left text-lg font-medium px-4 py-3 outline-black bg-gray-200 rounded-lg">
                Personal preferences
            </button>
            <div id="prefsForm" class="collapse-content" style="display: none;">
                <form method="POST" action="">
                    <!-- Goal -->
                    <div class="mb-4">
                        <label for="goal" class="block text-l font-medium mb-1">Goal</label>
                        <select id="goal" name="goal" class="select select-bordered w-full text-center" required>
                            <option value="muscle_gain" <?= $goal === 'muscle_gain' ? 'selected' : '' ?>>Muscle gain</option>
                            <option value="weight_loss" <?= $goal === 'weight_loss' ? 'selected' : '' ?>>Weight loss</option>
                            <option value="condition" <?= $goal === 'condition' ? 'selected' : '' ?>>Condition improvement</option>
                            <option value="balance" <?= $goal === 'balance' ? 'selected' : '' ?>>Balance and flexibility</option>
                        </select>
                    </div>
                    <!-- level -->
                    <div class="mb-4">
                        <label for="level" class="block text-l font-medium mb-1">Level</label>
                        <select id="level" name="level" class="select select-bordered w-full text-center" required>
                            <option value="beginner" <?= $level === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $level === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        </select>
                    </div>
                    <!-- equipment -->
                    <div class="mb-4">
                        <label for="equipment" class="block text-l font-medium mb-1">Equipment</label>
                        <input type="text" id="equipment" name="equipment" value="<?= htmlspecialchars($equipment) ?>" class="input input-bordered w-full text-center" placeholder="Say dumbbells, resistance band, none" required>
                    </div>
                    <!-- Time limit -->
                    <div class="mb-4">
                        <label for="time_limit" class="block text-l font-medium mb-1">Time limit (in minutes)</label>
                        <input type="number" id="time_limit" name="time_limit" value="<?= htmlspecialchars($time_limit) ?>" class="input input-bordered w-full text-center" required>
                    </div>
                    <!-- Focus area -->
                    <div class="mb-4">
                        <label for="focus_area" class="block text-l font-medium mb-1">Focus area</label>
                        <select id="focus_area" name="focus_area" class="select select-bordered w-full text-center" required>
                            <option value="full_body" <?= $focus_area === 'full_body' ? 'selected' : '' ?>>Full body</option>
                            <option value="arms" <?= $focus_area === 'arms' ? 'selected' : '' ?>>Arms</option>
                            <option value="legs" <?= $focus_area === 'legs' ? 'selected' : '' ?>>Legs</option>
                            <option value="core" <?= $focus_area === 'core' ? 'selected' : '' ?>>Core (stomach / back)</option>
                            <option value="cardio" <?= $focus_area === 'cardio' ? 'selected' : '' ?>>Cardio</option>
                        </select>
                    </div>

                    <button type="submit" name="save_settings" class="btn btn-primary w-full text-lg font-medium px-4 py-3 outline-black bg-gray-200 rounded-lg">Save settings</button>
                </form>
            </div>
        </div>
        <script src="src/JS/collapsible.js"></script>
    </section>
    <section>
        <div>
            <p id="current-date" class="my-4"></p>
            <script src="src/JS/date.js"></script>
        </div>
        <?php if (!empty($exercises)): ?>
            <div class="bg-green-200 rounded-lg shadow-lg p-6 ">
                <h2 class="text-xl font-semibold mb-2">Your personalized workout</h2>
                <div class="prose max-w-none whitespace-pre-line"><?= nl2br(htmlspecialchars($exercises)) ?></div>
            </div>
        <?php endif; ?>
    </section>

<!--    <div>-->
<!--        <p>Hier komt de chatbot</p>-->
<!--    </div>-->
</main>
<footer class="py-6 text-center text-gray-300 text-sm bg-[var(--header-nav)]">
    <p>©Nutricoach</p>
</footer>

<div id="settingsSidebar"
     class="fixed top-0 right-0 h-full w-96 bg-[var(--background)] shadow-lg transform translate-x-full transition-transform duration-300 z-50 flex flex-col">

    <!-- Header -->
    <div class="p-4 flex justify-between items-center border-b-2 border-b-black bg-[var(--header-nav)]">
        <h2 class="font-bold text-lg">Explore Nutricoach!</h2>
        <button id="closeSettings" class="text-gray-900 hover:text-black">&times;</button>
    </div>

    <!-- Menu links container -->
    <div class="flex-1 flex flex-col justify-start">

        <a href="nutricoach_chatbot.php"
           class="relative flex items-center justify-center gap-2 p-4 border-b border-black hover:bg-[var(--header-nav)] hover:text-white transition-all duration-200">
            <img src="src/images/nutricoach.png" alt="Chatbot" class="w-6 h-6 absolute left-4 top-1/2 -translate-y-1/2">
            <span class="text-black font-bold text-lg text-center w-full">Nutricoach Chatbot</span>
        </a>

        <a href="eating_pattern.php"
           class="relative flex items-center justify-center gap-2 p-4 border-b border-black hover:bg-[var(--header-nav)] hover:text-white transition-all duration-200">
            <img src="src/images/eatingpattern.png" alt="Plate with fork and knife"
                 class="w-6 h-6 absolute left-4 top-1/2 -translate-y-1/2">
            <span class="text-black font-bold text-lg text-center w-full">Eating Patterns</span>
        </a>

        <a href="mealslist.php"
           class="relative flex items-center justify-center gap-2 p-4 border-b border-black hover:bg-[var(--header-nav)] hover:text-white transition-all duration-200">
            <img src="src/images/meals.png" alt="Meals List" class="w-6 h-6 absolute left-4 top-1/2 -translate-y-1/2">
            <span class="text-black font-bold text-lg text-center w-full">Meals List</span>
        </a>

        <a href="exercises.php"
           class="relative flex items-center justify-center gap-2 p-4 border-b border-black hover:bg-[var(--header-nav)] hover:text-white transition-all duration-200">
            <img src="src/images/exercises.png" alt="Exercises"
                 class="w-6 h-6 absolute left-4 top-1/2 -translate-y-1/2">
            <span class="text-black font-bold text-lg text-center w-full">Exercises</span>
        </a>

        <!-- Last “coming soon” button that fills remaining space -->
        <p
                class="flex-1 flex items-center justify-center p-4">
            <span class="text-black font-bold text-lg text-center w-full">New sites coming soon...</span>
        </p>

    </div>
</div>

<div id="settingsBackdrop" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>
<script src="src/JS/settings.js"></script>

</body>
</html>
