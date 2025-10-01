<?php
//Function for a logged in user to get access
session_start();

// Check if the visitor is logged in
if (!isset($_SESSION['Login'])) {  // **GET**: Check if user is logged in (session variable)
    // Redirect if not logged in
    header("Location: login.php");
    exit;
}

global $db;
include 'includes/database.php'; // Verbind met je MySQL-database
// ================== BACKEND LOGIC ==================
// POST request met ?api=1 → sla nieuwe data op in database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true); // Ontvang JSON van frontend

// Bereid SQL statement voor
// Note: The ON DUPLICATE KEY UPDATE part seems to be missing some columns in the original code.
// I've corrected it to include all fields.
    $stmt = $db->prepare("INSERT INTO nutrition_data (user_id, fruit, vegetables, carbs, dairy, protein) VALUES (?, ?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE
                           fruit=VALUES(fruit), 
                           vegetables=VALUES(vegetables),
                           carbs=VALUES(carbs), 
                           dairy=VALUES(dairy), 
                           protein=VALUES(protein)");

// Note: Assuming user_id is an integer (i), the rest are strings (s).
    $stmt->bind_param(
        "isssss",
        $input['user_id'],
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
</head>
<body class="bg-[#FAF3DD] text-[#353831] min-h-screen flex flex-col items-center">

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

<!-- ✅ Koppel het externe JavaScript bestand -->
<script src="src/JS/nitro.js"></script>

</body>
</html>
