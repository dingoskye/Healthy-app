<?php
require_once 'includes/database.php';
session_start();

// Ensure user logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['id'];

$successMessage = '';
$errors = [];

// Helper: escape output
function e($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read POST values (using the same names as your register form)
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? ''); // expected YYYY-MM-DD
    $sex = trim($_POST['sex'] ?? '');
    $heightRaw = isset($_POST['height']) ? trim((string)$_POST['height']) : '';
    $weightRaw = isset($_POST['weight']) ? trim((string)$_POST['weight']) : '';
    $preferencesText = trim($_POST['preferences_text'] ?? '');
    $allergiesText = trim($_POST['allergies_text'] ?? '');

    // Basic validation (same flavour as register)
    if ($firstName === '') {
        $errors['firstName'] = 'First name is required.';
    }
    if ($lastName === '') {
        $errors['lastName'] = 'Last name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }
    if ($dateOfBirth === '' || DateTime::createFromFormat('Y-m-d', $dateOfBirth) === false) {
        $errors['dateOfBirth'] = 'A valid date of birth is required (YYYY-MM-DD).';
    }
    if ($sex === '') {
        $errors['sex'] = 'Please select sex.';
    }

    // Height/Weight: if blank -> store 0.00
    $heightFloat = ($heightRaw === '' ? 0.00 : (float)$heightRaw);
    $weightFloat = ($weightRaw === '' ? 0.00 : (float)$weightRaw);

    // Build preferences string consistent with profile.php
    $preferences = trim(
            ($preferencesText !== '' ? "Preferences: $preferencesText" : '') .
            ($preferencesText !== '' && $allergiesText !== '' ? "\n" : '') .
            ($allergiesText !== '' ? "Allergies: $allergiesText" : '')
    );

    if (empty($errors)) {
        // Update DB
        $query = "UPDATE users
                  SET first_name = ?, last_name = ?, email = ?, date_of_birth = ?, sex = ?,
                      height_cm = ?, weight_kg = ?, preferences = ?
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            $errors['database'] = 'Prepare failed: ' . $db->error;
        } else {
            // types: s s s s s d d s i
            $bindResult = $stmt->bind_param(
                    "sssssddsi",
                    $firstName,
                    $lastName,
                    $email,
                    $dateOfBirth,
                    $sex,
                    $heightFloat,
                    $weightFloat,
                    $preferences,
                    $userId
            );

            if ($bindResult === false) {
                $errors['database'] = 'Bind failed: ' . $stmt->error;
            } else {
                if ($stmt->execute()) {
                    $successMessage = 'Profile updated successfully.';
                } else {
                    $errors['database'] = 'Execute failed: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}

// Fetch current user data for form population
$query = "SELECT first_name, last_name, email, date_of_birth, sex, height_cm, weight_kg, preferences
          FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?: [];
$stmt->close();

// Split preferences/allergies from the single column
$preferencesPart = '';
$allergiesPart = '';
if (!empty($user['preferences'])) {
    $lines = preg_split("/\r\n|\n|\r/", $user['preferences']);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (strpos($line, "Preferences:") === 0) {
            $preferencesPart = trim(substr($line, strlen("Preferences:")));
        } elseif (strpos($line, "Allergies:") === 0) {
            $allergiesPart = trim(substr($line, strlen("Allergies:")));
        }
    }
}

// Helper to choose old POST value or DB value
function old($postKey, $dbValueKey, $user)
{
    if (isset($_POST[$postKey])) {
        return e($_POST[$postKey]);
    }
    return e($user[$dbValueKey] ?? '');
}

// Helper for height/weight: show empty when DB is 0 (or when POST left empty)
function oldNumber($postKey, $dbValueKey, $user)
{
    if (isset($_POST[$postKey])) {
        $val = trim((string)$_POST[$postKey]);
        return $val === '' ? '' : e($val);
    }
    $dbVal = $user[$dbValueKey] ?? null;
    // treat zero as "empty" for display
    return ($dbVal === null || floatval($dbVal) == 0.0) ? '' : e($dbVal);
}

// Helper for preferences textareas (prefer POST if present)
function oldTextarea($postKey, $fallback)
{
    if (isset($_POST[$postKey])) return e($_POST[$postKey]);
    return e($fallback);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[var(--background)] min-h-screen flex flex-col text-gray-800">
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

<main class="flex-grow flex items-center justify-center p-6">
    <div class="w-full max-w-2xl bg-white shadow-md rounded-lg p-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Edit profile</h1>

        <?php if ($successMessage): ?>
            <div class="mb-4 px-4 py-2 bg-green-500 text-white rounded-lg"><?= e($successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['database'])): ?>
            <div class="mb-4 px-4 py-2 bg-red-500 text-white rounded-lg"><?= e($errors['database']) ?></div>
        <?php endif; ?>

        <form action="update_profile.php" method="post" class="space-y-6" novalidate>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                    <input id="firstName" name="firstName" type="text"
                           value="<?= old('firstName', 'first_name', $user) ?>"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
                    <p class="block text-sm text-red-500 mt-1"><?= $errors['firstName'] ?? '' ?></p>
                </div>

                <div>
                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                    <input id="lastName" name="lastName" type="text" value="<?= old('lastName', 'last_name', $user) ?>"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
                    <p class="block text-sm text-red-500 mt-1"><?= $errors['lastName'] ?? '' ?></p>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" name="email" type="email" value="<?= old('email', 'email', $user) ?>"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
                <p class="block text-sm text-red-500 mt-1"><?= $errors['email'] ?? '' ?></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="dateOfBirth" class="block text-sm font-medium text-gray-700 mb-1">Date of birth</label>
                    <input id="dateOfBirth" name="dateOfBirth" type="date"
                           value="<?= old('dateOfBirth', 'date_of_birth', $user) ?>"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
                    <p class="block text-sm text-red-500 mt-1"><?= $errors['dateOfBirth'] ?? '' ?></p>
                </div>

                <div>
                    <label for="sex" class="block text-sm font-medium text-gray-700 mb-1">Sex</label>
                    <select id="sex" name="sex"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <?php
                        $selectedSex = isset($_POST['sex']) ? $_POST['sex'] : ($user['sex'] ?? '');
                        ?>
                        <option value="" <?= $selectedSex === '' ? 'selected' : '' ?>>Choose...</option>
                        <option value="male" <?= $selectedSex === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $selectedSex === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= $selectedSex === 'other' ? 'selected' : '' ?>>Other / Prefer not to
                            say
                        </option>
                    </select>
                    <p class="block text-sm text-red-500 mt-1"><?= $errors['sex'] ?? '' ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)
                        (optional)</label>
                    <input id="height" name="height" type="number" step="0.01" min="0"
                           value="<?= oldNumber('height', 'height_cm', $user) ?>"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
                </div>

                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)
                        (optional)</label>
                    <input id="weight" name="weight" type="number" step="0.01" min="0"
                           value="<?= oldNumber('weight', 'weight_kg', $user) ?>"
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="preferences" class="block text-sm font-medium text-gray-700 mb-1">
                        Eating preferences (optional)
                    </label>
                    <textarea id="preferences" name="preferences_text" rows="4"
                              class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="e.g. vegetarian, halal"><?= oldTextarea('preferences_text', $preferencesPart) ?></textarea>
                </div>

                <div>
                    <label for="allergies" class="block text-sm font-medium text-gray-700 mb-1">
                        Allergies (optional)
                    </label>
                    <textarea id="allergies" name="allergies_text" rows="4"
                              class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="e.g. peanuts, gluten"><?= oldTextarea('allergies_text', $allergiesPart) ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">Back to your <a href="profile.php"
                                                                   class="text-indigo-600 hover:underline">profile</a>
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    Save changes
                </button>
            </div>
        </form>
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
