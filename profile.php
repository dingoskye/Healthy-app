<?php
require_once 'includes/database.php';
session_start();

// Example: check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch current user info
$query = "SELECT first_name, last_name, email, date_of_birth, sex, height_cm, weight_kg, preferences 
          FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Split preferences back into two parts (simple split)
$preferencesPart = '';
$allergiesPart = '';
if (!empty($user['preferences'])) {
    $lines = explode("\n", $user['preferences']);
    foreach ($lines as $line) {
        if (str_starts_with($line, "Preferences:")) {
            $preferencesPart = trim(str_replace("Preferences:", "", $line));
        } elseif (str_starts_with($line, "Allergies:")) {
            $allergiesPart = trim(str_replace("Allergies:", "", $line));
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[var(--background)] min-h-screen flex flex-col text-gray-800">

<!-- Nav stays the same -->
<nav class="bg-[var(--header-nav)] text-white p-4 flex justify-between items-center">
    <span class="font-bold text-lg">
        <a href="index.php">Nutricoach</a>
    </span>
    <div class="space-x-4">
        <a href="#" id="settingsBtn" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>

<main class="flex-grow flex items-center justify-center p-6">
    <div class="w-full max-w-2xl bg-white shadow-md rounded-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Profile</h1>

        <form action="update_profile.php" method="post" class="space-y-6">
            <!-- Name fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" name="firstName" value="<?= htmlspecialchars($user['first_name']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" name="lastName" value="<?= htmlspecialchars($user['last_name']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"/>
                </div>
            </div>

            <!-- DOB -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                <input type="date" name="dateOfBirth" value="<?= htmlspecialchars($user['date_of_birth']) ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"/>
            </div>

            <!-- Height & Weight -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                    <input type="number" name="height" step="0.01" value="<?= htmlspecialchars($user['height_cm']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" value="<?= htmlspecialchars($user['weight_kg']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"/>
                </div>
            </div>

            <!-- Preferences & Allergies -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Eating Preferences</label>
                    <textarea name="preferences_text" rows="4"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= htmlspecialchars($preferencesPart) ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Allergies</label>
                    <textarea name="allergies_text" rows="4"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= htmlspecialchars($allergiesPart) ?></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
