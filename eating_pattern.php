<?php
session_start();

$currentWeek = date("W");
$daysOfWeek = ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"];
$timeSlots = ["08:00 - 09:00","12:00 - 12:30","18:00 - 19:00"];
$days = [];
foreach ($daysOfWeek as $day) {
    $days[$day] = array_fill_keys($timeSlots, []);
}

?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eating Pattern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
    <script src="src/JS/eating.js" defer></script>
</head>
<body class="bg-[var(--background)] text-gray-800 min-h-screen flex flex-col">

<nav class="bg-[var(--header-nav)] text-white p-4 flex justify-between items-center">
    <span class="font-bold text-lg">Eating Patterns</span>
    <div class="space-x-4">
        <a href="#" class="hover:underline">⚙️</a>
        <a href="#" class="hover:underline">🔔</a>
    </div>
</nav>

<main class="flex-1 p-6">

    <div id="food-modal"
         class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full relative">
            <h2 class="text-xl font-bold mb-2" id="modal-title"></h2>
            <img class="w-32 h-32 object-cover mx-auto mb-4" id="modal-img" src="" alt="Food">
            <p><strong>Merk:</strong> <span id="modal-brand"></span></p>
            <p><strong>Calorieën:</strong> <span id="modal-energy"></span></p>
            <p><strong>Suikers:</strong> <span id="modal-sugars"></span></p>
            <div class="mt-4 text-right">
                <button id="modal-close-btn"
                        class="bg-[var(--elements)] text-white px-4 py-2 rounded">Sluiten</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <section id="eating-area" class="md:col-span-2 space-y-6">
            <?php foreach ($days as $day => $meals): ?>
                <section class="day-section bg-[var(--text-block)] rounded-2xl p-4 shadow-lg" data-day="<?= $day ?>">
                    <h2 class="text-xl font-bold capitalize mb-2">
                        <span class="day-name"><?= ucfirst($day) ?></span>
                        <span class="text-sm text-gray-600 ml-2 day-date"></span>
                    </h2>

                    <?php foreach ($meals as $time => $foods): ?>
                        <?php $containerId = strtolower($day . '-' . $time); ?>
                        <div id="<?= str_replace([' ', ':'], '-', $containerId) ?>" class="time-slot border-b border-[var(--elements)] pb-2 mb-2" data-time="<?= $time ?>">
                            <p class="font-semibold"><?= $time ?></p>

                            <?php if (!empty($foods)): ?>
                                <ul class="list-disc list-inside text-gray-700">
                                    <?php foreach ($foods as $food): ?>
                                        <li><?= htmlspecialchars($food) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <button type="button"
                                    class="add-meal-btn bg-[var(--elements)] text-white rounded px-3 py-1 mt-2 hover:opacity-90"
                                    data-day="<?= $day ?>"
                                    data-time="<?= $time ?>">
                                Add here +
                            </button>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        </section>

        <aside class="md:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl p-4 shadow">
                <div class="flex items-center space-x-2 mb-2">
                    <button id="prevWeekBtn" type="button" class="px-2 py-1 bg-[var(--elements)] text-white rounded">&laquo;</button>
                    <input type="date" id="datePicker" max="<?= date('Y-m-d'); ?>" class="border rounded px-2 py-1 flex-1" />
                    <button id="nextWeekBtn" type="button" class="px-2 py-1 bg-[var(--elements)] text-white rounded">&raquo;</button>
                </div>
                <p class="text-sm text-gray-600">Kies een datum. Gebruik pijlen om weken te navigeren.</p>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow">
                <h3 class="font-bold mb-2">Agenda</h3>
                <p class="text-sm text-gray-600">Hier kun je later extra agenda-gegevens of events tonen.</p>
            </div>
        </aside>
    </div>
</main>

<!--AI coach. IS-->
<div id="ai-coach" class="fixed right-0 top-0 h-full w-80 transform translate-x-full transition-transform duration-300 z-50 bg-white border-l-4 border-[var(--elements)] shadow-lg">
    <div class="p-4 h-full flex flex-col">
        <div class="mb-2">
            <h3 class="text-lg font-bold">AI Coach</h3>
        </div>
        <div id="ai-messages" class="overflow-auto text-sm text-gray-700 flex-1 space-y-2">
            <p>Welkom! Ik geef je tips zodra je eten toevoegt.</p>
        </div>
        <div class="mt-3">
            <button id="ai-close-btn" class="w-full bg-[var(--elements)] text-white rounded px-3 py-2">Sluit coach</button>
        </div>
    </div>
</div>

<button id="ai-toggle-btn" class="fixed right-4 bottom-4 bg-[var(--elements)] text-white px-4 py-2 rounded shadow-lg z-60">💬 AI Coach</button>

<footer class="bg-[var(--header-nav)] text-white text-center p-3 mt-6">
    <p>@Team 1 - ChatBot</p>
</footer>

</body>
</html>