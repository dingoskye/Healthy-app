
<?php
// === Simple API logic ===
// If this is an API request (AJAX GET), return a JSON with stored data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $file = __DIR__ . '/nutrition_data.json';
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode([]);
    }
    exit;
}

// If this is an API POST to save new data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    file_put_contents(__DIR__ . '/nutrition_data.json', json_encode($input));
    echo json_encode(['status' => 'saved']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nutrition Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FAF3DD] text-[#353831] min-h-screen flex flex-col items-center p-6">

<header class="bg-[#8FC0A9] w-full p-4 rounded-2xl shadow-lg text-center mb-6">
    <h1 class="text-2xl font-bold">Hey there Anna,</h1>
    <p class="text-lg">Let’s track your nutritions</p>
</header>

<main id="tracker" class="w-full max-w-md space-y-6">
    <!-- Fruits -->
    <section class="bg-[#C8D5B9] p-4 rounded-xl shadow-md">
        <h2 class="font-semibold mb-3">How many fruits have you eaten today?</h2>
        <div id="fruit-options" class="flex justify-between">
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">0-1</button>
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">2-3</button>
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">4-5</button>
            <button class="fruit-btn bg-[#eec584] hover:bg-[#68B0AB] px-4 py-2 rounded-full">5+</button>
        </div>
    </section>

    <!-- Other inputs -->
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

    <button id="submit" class="w-full bg-[#00916E] hover:bg-[#0E1774] text-white font-bold py-3 rounded-xl shadow-lg">
        Save Nutrition
    </button>

    <div id="status" class="text-center text-sm mt-4"></div>
</main>

<script>
    window.addEventListener("load", () => init());

    const apiUrl = "index.php?api=1";
    let selectedFruit = "";
    let fruitButtons = [];
    let statusEl;

    const init = () => {
        fruitButtons = document.querySelectorAll(".fruit-btn");
        statusEl = document.getElementById("status");

        // Attach fruit button listeners
        fruitButtons.forEach(btn => {
            btn.addEventListener("click", () => selectFruit(btn.innerText));
        });

        // Load previous data from server
        fetchNutritionData();

        // Submit button
        document.getElementById("submit").addEventListener("click", () => saveNutritionData());
    };

    const selectFruit = (value) => {
        selectedFruit = value;
        fruitButtons.forEach(b => b.classList.remove("ring","ring-[#264653]"));
        fruitButtons.forEach(b => { if (b.innerText === value) b.classList.add("ring","ring-[#264653]"); });
    };

    const fetchNutritionData = () => {
        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                console.log("✅ Nutrition data received:", data);
                if (data.fruit) selectFruit(data.fruit);
                document.getElementById("vegetables").value = data.vegetables || "";
                document.getElementById("carbs").value = data.carbs || "";
                document.getElementById("dairy").value = data.dairy || "";
                document.getElementById("protein").value = data.protein || "";
            })
            .catch(err => {
                console.error("❌ Error fetching data:", err);
                statusEl.innerText = "Error loading data.";
            });
    };

    const saveNutritionData = () => {
        const payload = {
            fruit: selectedFruit,
            vegetables: document.getElementById("vegetables").value,
            carbs: document.getElementById("carbs").value,
            dairy: document.getElementById("dairy").value,
            protein: document.getElementById("protein").value
        };

        fetch(apiUrl, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(resp => {
                console.log("✅ Saved:", resp);
                statusEl.innerText = "Nutrition saved successfully!";
            })
            .catch(err => {
                console.error("❌ Error saving:", err);
                statusEl.innerText = "Error saving data.";
            });
    };
</script>

</body>
</html>
