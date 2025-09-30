<?php
include 'includes/database.php';
global $db;
$result = mysqli_query($db, "SELECT * FROM shop_products");
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Food Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FAF3DD] text-[#353831] min-h-screen flex flex-col">

<!-- HEADER -->
<header class="bg-[#264653] text-[#eec584] w-full p-4 shadow-lg text-center">
    <h1 class="text-3xl font-bold">🛒 Food Shop</h1>
    <p class="text-[#C8D5B9]">Shop for healthy food items</p>
</header>

<!-- MAIN CONTENT -->
<main class="flex-1 w-full max-w-6xl mx-auto px-4 py-6">

    <!-- ZOEKVELD -->
    <div class="mb-6">
        <input type="text" id="search" placeholder="Zoek product..."
               class="w-full px-4 py-2 rounded-lg border-2 border-[#C8D5B9] focus:outline-none focus:ring-2 focus:ring-[#eec584]" />
    </div>

    <!-- PRODUCT GRID -->
    <div id="products" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

        <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-[#C8D5B9] rounded-xl shadow-md hover:shadow-lg transition duration-300 overflow-hidden flex flex-col h-[400px]">
                <!-- Afbeelding -->
                <img src="<?php echo !empty($row['image']) ? htmlspecialchars($row['image']) : 'uploads/default.jpg'; ?>"
                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                     class="w-full h-48 object-cover border-b-4 border-[#8FC0A9]">

                <!-- Content -->
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="text-lg font-semibold mb-1 truncate"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p class="text-[#353831] text-sm mb-2 flex-1 overflow-hidden"><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="mb-2">Portie: <?php echo !empty($row['gram']) ? htmlspecialchars($row['gram']) : 'n.v.t.'; ?></p>
                    <strong class="block text-[#00916E] font-bold mb-3">€<?php echo number_format($row['price'], 2, ',', '.'); ?></strong>
                    <button class="w-full bg-[#eec584] hover:bg-[#68B0AB] text-[#353831] font-bold py-2 px-4 rounded-xl transition duration-300">
                        Toevoegen
                    </button>
                </div>
            </div>
        <?php endwhile; ?>

    </div>
</main>

<!-- FOOTER -->
<footer class="bg-[#264653] text-white w-full p-6 mt-auto text-center">
    &copy; <?php echo date('Y'); ?> Food Shop. Alle rechten voorbehouden.
</footer>

<!-- JS ZOEKFUNCTIE -->
<script>
    document.getElementById('search').addEventListener('keyup', function(){
        let filter = this.value.toLowerCase();
        document.querySelectorAll('#products > div').forEach(function(p){
            let name = p.querySelector('h3').textContent.toLowerCase();
            p.style.display = name.includes(filter) ? 'flex' : 'none'; // 'flex' om card height te behouden
        });
    });
</script>

</body>
</html>
