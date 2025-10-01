<?php
session_start();
require_once 'includes/database.php';

$userId = $_SESSION['id'] ?? null;
if ($userId === null) {
    header('Location: login.php');
    exit();
}

// Fetch last 14 days nutrition_data
$q = mysqli_query(
    $db,
    "SELECT created_at, fruit, vegetables, carbs, dairy, protein
     FROM nutrition_data
     WHERE user_id = " . (int)$userId . "
       AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
     ORDER BY created_at DESC"
);
$rows = [];
while ($r = mysqli_fetch_assoc($q)) {
    $rows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nutrition Analysis</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FAF3DD] text-[#353831] min-h-screen flex flex-col">

<nav class="w-full bg-[#356b4f] text-white p-4 flex justify-between">
    <a href="index.php" class="text-white font-extrabold text-2xl">Nutricoach</a>
    <a href="profile.php">Profile</a>
</nav>

<header class="bg-[#8FC0A9] w-full p-4 text-center shadow-lg">
    <h1 class="text-2xl font-bold">Nutrition Analysis</h1>
    <p class="text-lg">Your last two weeks with AI feedback</p>
</header>

<main class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
    <!-- LEFT SIDE: Nutrition Data -->
    <section class="bg-[#C8D5B9] p-4 rounded-lg shadow-md overflow-auto">
        <h2 class="text-xl font-bold mb-4">Last 14 Days</h2>
        <table class="w-full border border-gray-300 rounded-lg overflow-hidden text-sm">
            <thead class="bg-[#68B0AB] text-white">
            <tr>
                <th class="p-2 text-left">Date</th>
                <th class="p-2">Fruit</th>
                <th class="p-2">Veg</th>
                <th class="p-2">Carbs</th>
                <th class="p-2">Dairy</th>
                <th class="p-2">Protein</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr class="border-t">
                    <td class="p-2"><?= htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))) ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['fruit']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['vegetables']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['carbs']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['dairy']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['protein']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- RIGHT SIDE: AI Analysis -->
    <section class="bg-[#C8D5B9] p-4 rounded-lg shadow-md flex flex-col">
        <h2 class="text-xl font-bold mb-4">AI Feedback</h2>
        <div id="aiFeedback" class="flex-1 overflow-y-auto whitespace-pre-line text-gray-800">
            <p class="animate-pulse text-gray-600">Analyzing your nutrition data...</p>
        </div>
    </section>
</main>

<script>
    document.addEventListener("DOMContentLoaded", async () => {
        try {
            // Send a request to your chat proxy with a system prompt
            const response = await fetch("api/nutrition_coach.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    model: "gpt-4o-mini",
                    messages: [
                        {
                            role: "system",
                            content: "You are a nutrition coach AI. Analyze the user's last 2 weeks of nutrition data, and give personalized feedback. Be encouraging but also specific about improvements."
                        },
                        { role: "user", content: "Please analyze my nutrition logs and give me feedback." }
                    ]
                })
            });

            const data = await response.json();
            const feedbackEl = document.getElementById("aiFeedback");

            if (data.reply) {
                feedbackEl.textContent = data.reply;
            } else {
                feedbackEl.textContent = "No feedback could be generated.";
            }
        } catch (err) {
            document.getElementById("aiFeedback").textContent =
                "⚠️ Error analyzing data: " + err.message;
        }
    });
</script>

</body>
</html>
