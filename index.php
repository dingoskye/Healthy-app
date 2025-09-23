<?php
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="src/style.css" rel="stylesheet">
    <title>Nutricoach</title>
</head>
<body class="bg-[var(--background)] text-gray-800 min-h-screen flex flex-col">

<nav class="bg-[var(--header-nav)] text-white p-4 flex justify-between items-center">
    <span class="font-bold text-lg">Nutricoach</span>
    <div class="space-x-4">
        <!-- Settings button -->
        <a href="#" id="settingsBtn" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>

<header>

    <section class="header-container">
            <!--            In de header komt het icoon wat een button is, daarnaast hebben we rechts 2 buttons zitten.-->
            <!--            Settings & Notificaties. -->
    </section>

</header>

<main class="flex-1 p-4 space-y-6">

    <section>
        <!--        In de main komt de chat met de chatbot, Aan het begin zal er een bericht staan van; "Vul een prompt in-->
        <!--        zodat ik je kan helpen! En als er eenmaal iets is gegeven is het gewoon een chat.-->
        <!--        Maak van Settings een pop up slide van de zijkant, zo kan je prgm 3 gebruiken.-->
    </section>

</main>

<footer class="bg-[var(--header-nav)] text-white text-center p-3">
    <!-- in de footer komen de Send knop en chatbox -->
</footer>

<!-- Hidden sidebar -->
<div id="settingsSidebar"
     class="fixed top-0 right-0 h-full w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <div class="p-4 flex justify-between items-center border-b">
        <h2 class="font-bold text-lg">Settings</h2>
        <button id="closeSettings" class="text-gray-600 hover:text-gray-900">&times;</button>
    </div>
    <div class="p-4 space-y-4">
        <p>Settings content goes here...</p>
        <label class="block">
            <span class="text-gray-700">Option 1</span>
            <input type="checkbox" class="mt-1">
        </label>
        <label class="block">
            <span class="text-gray-700">Option 2</span>
            <input type="checkbox" class="mt-1">
        </label>
    </div>
</div>

<div id="settingsBackdrop" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>
<script src="src/JS/settings.js"></script>
</body>
</html>


