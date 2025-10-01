<?php
session_start();
require_once 'includes/database.php';

$userId = $_SESSION['id'] ?? null;

if ($userId === null) {
    header('Location: login.php');
    exit();
}

$errors = [];

if (isset ($_POST['submit'])) {
    // form velden
    $fruits = $_POST['fruits'];
    $vegetables = $_POST['vegetables'];
    $carbs = $_POST['carbs'];
    $dairy = $_POST['dairy'];
    $protein = $_POST['protein'];
    $createdAt = date('Y-m-d H:i:s');

    if (empty($errors)) {
        $query = "INSERT INTO nutrition_data 
          (user_id, fruit, vegetables, carbs, dairy, protein, created_at) 
          VALUES ('$userId', '$fruits', '$vegetables', '$carbs', '$dairy', '$protein', '$createdAt')";

    }

    $result = mysqli_query($db, $query);

    if ($result) {
        header('Location: nutricoach_analysis.php');
        exit;
    } else {
        $errors['db'] = "Errors inserting into meals table.";
    }
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
    <h1 class="text-2xl font-bold">Hey there,</h1>
    <p class="text-lg">Let’s track your nutritions</p>
</header>

<form action="" method="post">
    <!-- ===== MAIN CONTENT ===== -->
    <main id="tracker" class="w-full max-w-md space-y-6">

        <!-- ✅ Statusmeldingen boven het formulier -->
        <div id="status" class="text-center text-sm mb-4 font-semibold"></div>

        <!-- ====== Fruit-keuze knoppen ====== -->
        <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
            <h2 class="font-semibold mb-3">How many fruits have you eaten today?</h2>
            <div id="fruit-options" class="flex justify-between">
                <label>
                    <input type="radio" name="fruits" value="0-1" class="hidden peer">
                    <span class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full peer-checked:bg-[#68B0AB]">
                    0-1
                </span>
                </label>
                <label>
                    <input type="radio" name="fruits" value="2-3" class="hidden peer">
                    <span class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full peer-checked:bg-[#68B0AB]">
                    2-3
                </span>
                </label>
                <label>
                    <input type="radio" name="fruits" value="4-5" class="hidden peer">
                    <span class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full peer-checked:bg-[#68B0AB]">
                    4-5
                </span>
                </label>
                <label>
                    <input type="radio" name="fruits" value="5+" class="hidden peer">
                    <span class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full peer-checked:bg-[#68B0AB]">
                    5+
                </span>
                </label>
            </div>
        </section>

        <!-- ✅ Tekstvelden voor overige categorieën -->
        <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
            <h2 class="font-semibold mb-3">What vegetables have you had so far?</h2>
            <input id="vegetables" type="text" name="vegetables" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
        </section>

        <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
            <h2 class="font-semibold mb-3">Did you eat something with bread, rice, pasta, or potatoes today?</h2>
            <input id="carbs" type="text" name="carbs" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
        </section>

        <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
            <h2 class="font-semibold mb-3">Have you had milk, cheese, yogurt, or a dairy alternative yet?</h2>
            <input id="dairy" type="text" name="dairy" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
        </section>

        <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
            <h2 class="font-semibold mb-3">What beans, lentils, eggs, fish, or meat have you eaten today?</h2>
            <input id="protein" type="text" name="protein" class="w-full p-2 rounded-lg border border-[#4A7C59]" />
        </section>

        <!-- ✅ Opslaan-knop -->
        <button id="submit" type="submit" name="submit" class="w-full bg-[#00916E] hover:bg-[#0E1774] text-white font-bold py-3 rounded-xl shadow-lg">
            Save Nutrition
        </button>

        <!-- ✅ Statusmeldingen (bijv. “saved successfully”) -->
        <div id="status" class="text-center text-sm mt-4"></div>
    </main>
</form>

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

</body>
</html>
