<?php
session_start();

$days = [
        "monday" => [
                "08:00 - 09:00" => ["Bread", "Cheese", "Apple"],
                "12:00 - 12:30" => ["Soup", "Granola Bar"],
                "18:00 - 19:00" => ["Rice", "Lasagna", "Ice Cream"],
        ],
        "tuesday" => [
                "08:00 - 09:00" => ["Bread", "Mango", "Nutella"],
                "12:00 - 12:30" => []
        ]
];

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eating Pattern</title>
    <script src="src/JS/eating.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>

<body class="bg-[var(--background)] text-gray-800 min-h-screen flex flex-col">

<nav class="bg-[var(--header-nav)] text-white p-4 flex justify-between items-center">
    <span class="font-bold text-lg">Eating Patterns</span>
    <div class="space-x-4">
        <a href="#" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>

<main class="flex-1 p-4 space-y-6">
    <?php foreach ($days as $day => $meals): ?>
        <section class="bg-[var(--text-block)] rounded-2xl p-4 shadow-lg">
            <h2 class="text-xl font-bold capitalize mb-4"><?= $day ?></h2>
            <?php foreach ($meals as $time => $foods): ?>
                <?php $containerId = strtolower($day . '-' . $time); ?>
                <div id="<?= str_replace([' ', ':'], '-', $containerId) ?>" class="border-b border-[var(--elements)] pb-2 mb-2">
                    <p class="font-semibold"><?= $time ?></p>
                    <?php if (!empty($foods)): ?>
                        <ul class="list-disc list-inside text-gray-700">
                            <?php foreach ($foods as $food): ?>
                                <li><?= $food ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <button class="bg-[var(--elements)] text-white rounded px-3 py-1 mt-2 hover:opacity-90"
                            onclick="addMeal('<?= $day ?>','<?= $time ?>')">
                        Add here +
                    </button>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>

<!--    ai komt hier. IS-->
    <div class="bg-white rounded-2xl p-4 shadow-lg">
        <h3 class="font-bold text-lg mb-2">Your AI Coach</h3>
        <p class="text-gray-700">Tips, tops & suggestions will appear here to help you eat healthier 🥦💡</p>
    </div>
</main>

<footer class="bg-[var(--header-nav)] text-white text-center p-3">
    @Team 1 - ChatBot
</footer>

</body>
</html>
