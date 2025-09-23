<?php
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Exercises</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[var(--background)] text-gray-800 min-h-screen flex flex-col">
<nav>
    <a href="index.php"><img src="" alt="logo"></a>
    <div class="space-x-4">
        <a href="#" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>
<header class="flex p-4 space-y-6">
    <h1>Fitness exercises</h1>
    <p>These exercises are based on age, skill level
        and personal preferences</p>
</header>
<main class="flex-1 p-4 space-y-6">
    <p id="current-date"></p>
    <script src="src/JS/date.js"></script>

    <div>
        <p>Hier komt de chatbot</p>
    </div>
</main>
<footer>
    <p>©Nutricoach</p>
</footer>
</body>
</html>
