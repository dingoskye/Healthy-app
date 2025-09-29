<?php
// Function for a logged in user to get access
session_start();

// Check if the visitor is logged in
if (!isset($_SESSION['user'])) {  // **GET**: Check if user is logged in (session variable)
    // Redirect if not logged in
    header("Location: login.php");
    exit;
}

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
        <div class="max-w-[780px] mx-auto min-h-screen grid grid-rows-[auto_1fr_auto] gap-3 p-4">
            <!-- Header -->
            <header class="flex items-center justify-between">
                <h1 class="text-[18px] tracking-wide m-0">Nutricoach!</h1>
                <div class="flex gap-2 items-center">
                    <label for="model" class="sr-only">Model</label>
                    <select id="model"
                            class="bg-[#171a2b] text-[#e8ebf1] border border-[#22263d] rounded-[10px] px-3 py-2">
                        <option value="gpt-4o-mini">gpt-4o-mini</option>
                        <option value="gpt-4o">gpt-4o</option>
                    </select>
                </div>
            </header>

            <!-- Chat -->
            <div class="bg-[#171a2b] border border-[#1e2340] rounded-2xl p-3 grid grid-rows-[1fr_auto] overflow-hidden">
                <div id="messages"
                     class="overflow-y-auto p-2 flex flex-col gap-3 max-h-[60vh] text-[#e8ebf1]"
                     aria-live="polite"></div>


                <div class="grid grid-cols-[1fr_auto] gap-2 p-2 border-t border-dashed border-[#2a304f]">
        <textarea id="input"
                  class="resize-none min-h-[48px] max-h-[160px] bg-[#0e1120] text-[#e8ebf1] border border-[#1f2442] rounded-xl p-3"
                  placeholder="Type your message and press Enter..." autofocus></textarea>
                    <button id="sendBtn"
                            class="bg-[#4f7dff] text-white rounded-xl px-4 py-2 font-semibold disabled:opacity-60">
                        Send
                    </button>
                </div>
            </div>

            <p class="text-[#9aa4b2] text-xs mt-1">
                Requests gaan via <code>api/chat.php</code> zodat je OpenAI sleutel op de server blijft.
            </p>
        </div>

        <script src="src/JS/chatbot.js"></script>
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


