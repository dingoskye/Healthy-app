<?php
require_once 'includes/database.php';
session_start();

// Make sure the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['id'];

// Fetch current user info (no ?id= in url needed)
$query = "SELECT first_name, last_name, email, date_of_birth, sex, height_cm, weight_kg, preferences
          FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// If no user is found, redirect to login
if (!$user) {
    header("Location: login.php");
    exit;
}

// Split preferences for db
$preferencesPart = '';
$allergiesPart = '';
if (!empty($user['preferences'])) {
    $lines = preg_split("/\r\n|\n|\r/", $user['preferences']);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (function_exists('str_starts_with')) {
            if (str_starts_with($line, "Preferences:")) {
                $preferencesPart = trim(substr($line, strlen("Preferences:")));
            } elseif (str_starts_with($line, "Allergies:")) {
                $allergiesPart = trim(substr($line, strlen("Allergies:")));
            }
        } else {
            // fallback for older PHP versions
            if (strpos($line, "Preferences:") === 0) {
                $preferencesPart = trim(substr($line, strlen("Preferences:")));
            } elseif (strpos($line, "Allergies:") === 0) {
                $allergiesPart = trim(substr($line, strlen("Allergies:")));
            }
        }
    }
}

// Calculate age
$age = '-';
$dobFormatted = '-';
if (!empty($user['date_of_birth']) && strtotime($user['date_of_birth']) !== false) {
    try {
        $dob = new DateTime($user['date_of_birth']);
        $now = new DateTime();
        $age = $dob->diff($now)->y;
        $dobFormatted = $dob->format('d/m/Y');
    } catch (Exception $e) {
        $age = '-';
        $dobFormatted = '-';
    }
}

// Makes the form have htmlspecialchars so Cross-site scripting doesn't work.
function e($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[var(--background)] min-h-screen flex flex-col text-gray-800">

<!-- Nav -->
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

<main class="flex-grow flex flex-col items-center p-6">

    <!-- Profile header -->
    <div class="w-full max-w-md bg-[#3e6b4f] rounded-lg p-6 text-center text-white shadow-md">
        <!-- Profile picture placeholder -->
        <div class="w-28 h-28 rounded-full bg-gray-300 mx-auto mb-4"></div>

        <!-- Name -->
        <div class="bg-gray-100 text-gray-900 font-bold px-4 py-2 rounded-lg inline-block mb-2">
            <?= e($user['first_name'] . ' ' . $user['last_name']) ?>
        </div>

        <!-- DOB + Age -->
        <div class="bg-gray-100 text-gray-900 px-4 py-2 rounded-lg inline-block mb-4">
            <?= e($dobFormatted) ?> (<?= e($age) ?> years)
        </div>

        <!-- Logout -->
        <form action="logout.php" method="post" class="mb-6">
            <button type="submit"
                    class="px-6 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600">
                LOG OUT
            </button>
        </form>

        <hr class="border-gray-300 mb-6">

        <h2 class="text-lg font-bold mb-4">Optional Information</h2>

        <!-- Read-only details -->
        <div class="space-y-6 text-left">

            <!-- Contact & Personal -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Email</label>
                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">
                        <?= e($user['email']) ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Sex</label>
                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">
                        <?= e($user['sex'] ?? '-') ?>
                    </p>
                </div>
            </div>

            <!-- Height & Weight -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Height (cm)</label>
                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">
                        <?= e($user['height_cm'] !== null && $user['height_cm'] !== '' ? $user['height_cm'] : '-') ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Weight (kg)</label>
                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">
                        <?= e($user['weight_kg'] !== null && $user['weight_kg'] !== '' ? $user['weight_kg'] : '-') ?>
                    </p>
                </div>
            </div>

            <!-- Diet & Allergies -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Diet / Preferences</label>
                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">
                        <?= e($preferencesPart ?: '-') ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Allergies</label>
                    <p class=" bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">
                        <?= e($allergiesPart ?: '-') ?>
                    </p>
                </div>
            </div>

            <!-- Extra fields (placeholders) -->
            <!--            <div class="grid grid-cols-2 gap-4">-->
            <!--                <div>-->
            <!--                    <label class="block text-sm font-medium text-gray-100 mb-1">Extra</label>-->
            <!--                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">-</p>-->
            <!--                </div>-->
            <!--                <div>-->
            <!--                    <label class="block text-sm font-medium text-gray-100 mb-1">Extra</label>-->
            <!--                    <p class="bg-gray-100 text-gray-900 px-3 py-2 rounded-lg">-</p>-->
            <!--                </div>-->
            <!--            </div>-->

            <!-- Edit button -->
            <div class="flex justify-center">
                <a href="update_profile.php"
                   class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</main>

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