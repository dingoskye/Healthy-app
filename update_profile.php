<?php
require_once 'includes/database.php';
session_start();

// Make sure user is logged in.
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['id'];

$successMessage = '';
$errorMessage = '';

// Handle form submission (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $height = $_POST['height'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $preferences_text = trim($_POST['preferences_text'] ?? '');
    $allergies_text = trim($_POST['allergies_text'] ?? '');

    // Rebuild preferences string
    $preferencesCombined = '';
    if ($preferences_text !== '') {
        $preferencesCombined .= "Preferences: " . $preferences_text . "\n";
    }
    if ($allergies_text !== '') {
        $preferencesCombined .= "Allergies: " . $allergies_text;
    }

    //Prepares and does the UPDATE query, updating the users profile and returns succes or error messages.
    $query = "UPDATE users 
              SET height_cm = ?, weight_kg = ?, preferences = ?
              WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ddsi", $height, $weight, $preferencesCombined, $userId);
        if ($stmt->execute()) {
            $successMessage = "Profile updated successfully!";
        } else {
            $errorMessage = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Error preparing statement: " . $db->error;
    }
}

// Fetch current data again (for form display).
$query = "SELECT first_name, last_name, email, date_of_birth, sex, height_cm, weight_kg, preferences
          FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Split preferences for db.
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

// Makes the form have htmlspecialchars so Cross-site scripting doesnt work.
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
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[var(--background)] min-h-screen flex flex-col text-gray-800">

<main class="flex-grow flex flex-col items-center p-6">
    <div class="w-full max-w-md bg-[#3e6b4f] rounded-lg p-6 text-center text-white shadow-md">
        <h2 class="text-lg font-bold mb-4">Edit Profile</h2>

        <!-- Success / Error messages -->
        <?php if ($successMessage): ?>
            <div class="mb-4 px-4 py-2 bg-green-500 text-white rounded-lg">
                <?= e($successMessage) ?>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="mb-4 px-4 py-2 bg-red-500 text-white rounded-lg">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form action="update_profile.php" method="post" class="space-y-6 text-left">

            <!-- Height & Weight -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Height (cm)</label>
                    <input type="number" name="height" step="0.01"
                           value="<?= e($user['height_cm']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01"
                           value="<?= e($user['weight_kg']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"/>
                </div>
            </div>

            <!-- Diet & Allergies -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Diet</label>
                    <textarea name="preferences_text" rows="2"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"><?= e($preferencesPart) ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Allergies</label>
                    <textarea name="allergies_text" rows="2"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"><?= e($allergiesPart) ?></textarea>
                </div>
            </div>

            <!-- Save button -->
            <div class="flex justify-center">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>

            <!-- Back to profile -->
            <div class="flex justify-center mt-4">
                <a href="profile.php"
                   class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Back to Profile
                </a>
            </div>
        </form>
    </div>
</main>
</body>
</html>