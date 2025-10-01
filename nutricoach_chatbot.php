<?php
//// ================== BACKEND LOGIC ==================
//// Dit PHP-gedeelte verwerkt de API-aanroepen (GET & POST)
//
//// ✅ Als het een GET-aanvraag is met ?api=1 → stuur JSON met opgeslagen data terug
//if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
//    header('Content-Type: application/json');
//    $file = __DIR__ . '/nutrition_data.json'; // Bestand waarin data wordt bewaard
//    if (file_exists($file)) {
//        echo file_get_contents($file);       // Stuur bestaande data terug
//    } else {
//        echo json_encode([]);                // Als geen data → stuur lege JSON
//    }
//    exit;
//}
//
//// ✅ Als het een POST-aanvraag is met ?api=1 → sla nieuwe data op
//if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
//    header('Content-Type: application/json');
//    $input = json_decode(file_get_contents('php://input'), true); // Ontvang JSON van frontend
//    file_put_contents(__DIR__ . '/nutrition_data.json', json_encode($input)); // Opslaan in bestand
//    echo json_encode(['status' => 'saved']);  // Bevestiging terugsturen
//    exit;
//}

global $db;
include 'includes/database.php'; // Verbind met je MySQL-database
// ================== BACKEND LOGIC ==================
// POST request met ?api=1 → sla nieuwe data op in database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true); // Ontvang JSON van frontend

    // Bereid SQL statement voor
    $stmt = $db->prepare("INSERT INTO nutrition_data (fruit, vegetables, carbs, dairy, protein) VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE
                           fruit=VALUES(fruit), vegetables=VALUES(vegetables),
                           carbs=VALUES(carbs), dairy=VALUES(dairy), protein=VALUES(protein)");

    $stmt->bind_param(
        "sssss",
        $input['fruit'],
        $input['vegetables'],
        $input['carbs'],
        $input['dairy'],
        $input['protein']
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'saved']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
    $db->close();
    exit;
}

// GET request met ?api=1 → haal data uit database
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
    header('Content-Type: application/json');

    $result = $db->query("SELECT * FROM nutrition_data ORDER BY id DESC LIMIT 1"); // laatste rij
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode([]);
    }

    $db->close();
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nutrition Tracker</title>
    <!-- TailwindCSS voor snelle styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/JS/nitro.js">
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[#FAF3DD] text-[#353831] min-h-screen flex flex-col items-center">
<nav class="w-full bg-[#356b4f] text-white">
    <div class="w-full mx-auto px-6 flex items-center justify-between h-16">
        <!-- links: logo / titel -->
        <a href="index.php"
           class="text-white font-extrabold text-2xl transition-transform duration-200 hover:scale-110">
            Nutricoach
        </a>

        <!-- rechts: icons -->
        <div class="flex items-center space-x-4">
            <!-- menu button -->
            <button id="settingsBtn" class="p-2 rounded hover:bg-white/10 transition-colors">
                <img src="src/images/menu.png" alt="Menu" class="w-6 h-6">
            </button>

            <!-- profile link -->
            <a href="profile.php" class="p-2 rounded hover:bg-white/10 transition-colors">
                <img src="src/images/user.png" alt="Profile" class="w-6 h-6">
            </a>
        </div>
    </div>
</nav>

<!-- ===== HEADER ===== -->
<header class="bg-[#8FC0A9] w-full p-4 rounded shadow-lg text-center mb-6">
    <h1 class="text-2xl font-bold">Hey there Anna,</h1>
    <p class="text-lg">Let’s track your nutritions</p>
</header>

<!-- ===== MAIN CONTENT ===== -->
<main id="tracker" class="w-full max-w-md space-y-6">

    <!-- ✅ Statusmeldingen boven het formulier -->
    <div id="status" class="text-center text-sm mb-4 font-semibold"></div>

    <!-- ====== Fruit-keuze knoppen ====== -->
    <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
        <h2 class="font-semibold mb-3">How many fruits have you eaten today?</h2>
        <div id="fruit-options" class="flex justify-between">
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">0-1</button>
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">2-3</button>
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">4-5</button>
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">5+</button>
        </div>
    </section>

    <!-- ✅ Tekstvelden voor overige categorieën -->
    <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
        <h2 class="font-semibold mb-3">What vegetables have you had so far?</h2>
        <input id="vegetables" type="text" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
    </section>

    <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
        <h2 class="font-semibold mb-3">Did you eat something with bread, rice, pasta, or potatoes today?</h2>
        <input id="carbs" type="text" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
    </section>

    <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
        <h2 class="font-semibold mb-3">Have you had milk, cheese, yogurt, or a dairy alternative yet?</h2>
        <input id="dairy" type="text" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
    </section>

    <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
        <h2 class="font-semibold mb-3">What beans, lentils, eggs, fish, or meat have you eaten today?</h2>
        <input id="protein" type="text" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
    </section>

    <!-- ✅ Opslaan-knop -->
    <button id="submit" class="w-full bg-[#00916E] hover:bg-[#0E1774] text-white font-bold py-3 rounded-xl shadow-lg">
        Save Nutrition
    </button>

    <!-- ✅ Statusmeldingen (bijv. “saved successfully”) -->
    <div id="status" class="text-center text-sm mt-4"></div>
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

<!-- ✅ Koppel de externe JavaScript bestanden -->
<script src="src/JS/settings.js"></script>
<script src="src/JS/nitro.js"></script>

</body>
</html>
