<?php
session_start();



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
<nav>
    <span class="font-bold text-lg">
        <a href="index.php">Nutricoach</a>
    </span>
    <div>
        <a href="#" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>
<header>
    <h1>Fitness exercises</h1>
    <p>These exercises are based on age, skill level
        and personal preferences</p>
</header>
<main>
    <section>
        <!--- Hier komen de instellingen --->
        <div class="collapse collapse-arrow bg-base-200 shadow rounded-lg my-4">
            <input type="checkbox" id="settings-collapse" />
            <div class="collapse-title text-lg font-medium">
                Personal preferences
            </div>
            <div class="collapse-content">
                <form method="POST" action="">
                    <!-- Goal -->
                    <div class="mb-4">
                        <label for="goal" class="block text-sm font-medium mb-1">Goal</label>
                        <select id="goal" name="goal" class="select select-bordered w-full" required>
                            <option value="muscle_gain" <?= $goal === 'muscle_gain' ? 'selected' : '' ?>>Muscle gain</option>
                            <option value="weight_loss" <?= $goal === 'weight_loss' ? 'selected' : '' ?>>Weight loss</option>
                            <option value="condition" <?= $goal === 'condition' ? 'selected' : '' ?>>Condition improvement</option>
                            <option value="balance" <?= $goal === 'balance' ? 'selected' : '' ?>>Balance and flexibility</option>
                        </select>
                    </div>
                    <!-- level -->
                    <div class="mb-4">
                        <label for="level" class="block text-sm font-medium mb-1">Level</label>
                        <select id="level" name="level" class="select select-bordered w-full" required>
                            <option value="beginner" <?= $level === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $level === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        </select>
                    </div>
                    <!-- equipment -->
                    <div class="mb-4">
                        <label for="equipment" class="block text-sm font-medium mb-1">equipment</label>
                        <input type="text" id="equipment" name="equipment" value="<?= htmlspecialchars($equipment) ?>" class="input input-bordered w-full" placeholder="Say dumbbells, resistance band, none" required>
                    </div>
                    <!-- Time limit -->
                    <div class="mb-4">
                        <label for="time_limit" class="block text-sm font-medium mb-1">Time limit (in minutes)</label>
                        <input type="number" id="time_limit" name="time_limit" value="<?= htmlspecialchars($time_limit) ?>" class="input input-bordered w-full" required>
                    </div>

                    <div class="mb-4">
                        <label for="focus_area" class="block text-sm font-medium mb-1">Focus area</label>
                        <select id="focus_area" name="focus_area" class="select select-bordered w-full" required>
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
    </section>
    <section>
        <div>
            <p id="current-date"></p>
            <script src="src/JS/date.js"></script>
        </div>
        <!--- hier komen de ai gegenereerde oefeningen


        --->
    </section>

    <div>
        <p>Hier komt de chatbot</p>
    </div>
</main>
<footer>
    <p>©Nutricoach</p>
</footer>
</body>
</html>
