<?php
require_once 'includes/database.php';
session_start();
//var_dump($_SESSION);
//exit;
// checks if the user is logged in.
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['id'];

// Fetches the current user info so no ?id=5.
$query = "SELECT first_name, last_name, email, date_of_birth, sex, height_cm, weight_kg, preferences
          FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Split preferences back into two parts.
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

// Calculates the age.
$age = '';
if (!empty($user['date_of_birth'])) {
    $dob = new DateTime($user['date_of_birth']);
    $now = new DateTime();
    $age = $dob->diff($now)->y;
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

<!-- Nav -->
<nav class="bg-[var(--header-nav)] text-white p-4 flex justify-between items-center">
    <span class="font-bold text-lg">
        <a href="index.php">Nutricoach</a>
    </span>
    <div class="space-x-4">
        <a href="#" id="settingsBtn" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>

<main class="flex-grow flex flex-col items-center p-6">

    <!-- Profile header -->
    <div class="w-full max-w-md bg-[#3e6b4f] rounded-lg p-6 text-center text-white shadow-md">
        <!-- Profile picture placeholder -->
        <div class="w-28 h-28 rounded-full bg-gray-300 mx-auto mb-4"></div>

        <!-- Name -->
        <div class="bg-gray-100 text-gray-900 font-bold px-4 py-2 rounded-lg inline-block mb-2">
            <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?>
        </div>

        <!-- DOB + Age -->
        <div class="bg-gray-100 text-gray-900 px-4 py-2 rounded-lg inline-block mb-4">
            <?= htmlspecialchars(date("d/m/Y", strtotime($user['date_of_birth']))) ?>
            (<?= $age ?> years)
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

        <!-- Editable form -->
        <form action="update_profile.php" method="post" class="space-y-6 text-left">

            <!-- Height & Weight -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Height (cm)</label>
                    <input type="number" name="height" step="0.01"
                           value="<?= htmlspecialchars($user['height_cm']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01"
                           value="<?= htmlspecialchars($user['weight_kg']) ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"/>
                </div>
            </div>

            <!-- Diet & Allergies -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Diet</label>
                    <textarea name="preferences_text" rows="2"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"><?= htmlspecialchars($preferencesPart) ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Allergies</label>
                    <textarea name="allergies_text" rows="2"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"><?= htmlspecialchars($allergiesPart) ?></textarea>
                </div>
            </div>

            <!-- Extra fields -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Extra</label>
                    <input type="text" name="extra1"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-100 mb-1">Extra</label>
                    <input type="text" name="extra2"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900"/>
                </div>
            </div>

            <!-- Save button -->
            <div class="flex justify-center">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</main>

</body>
</html>