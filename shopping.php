
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
<header class="bg-[#264653] text-[#eec584] w-full p-4 shadow-lg flex justify-between items-center">
    <div class="text-center">
        <h1 class="text-3xl font-bold">🛒 Food Shop</h1>
        <p class="text-[#C8D5B9]">Shop for healthy food items</p>
    </div>
    <!-- Basket button -->
    <a href="basket.php" class="relative bg-[#eec584] text-[#264653] font-bold py-2 px-4 rounded-lg hover:bg-[#8FC0A9] transition">
        Winkelmand
        <span id="basket-count" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">0</span>
    </a>
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
                    <button
                            class="add-to-basket w-full bg-[#eec584] hover:bg-[#68B0AB] text-[#353831] font-bold py-2 px-4 rounded-xl transition duration-300"
                            data-id="<?php echo $row['id']; ?>"
                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                            data-price="<?php echo $row['price']; ?>"
                            data-image="<?php echo htmlspecialchars($row['image']); ?>">
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

<!-- JS SHOP LOGIC -->
<script>
    // Basket helpers
    function getBasket(){
        return JSON.parse(localStorage.getItem("basket") || "[]");
    }
    function saveBasket(basket){
        localStorage.setItem("basket", JSON.stringify(basket));
        updateBasketCount();
    }
    function updateBasketCount(){
        const basket = getBasket();
        document.getElementById("basket-count").textContent = basket.length;
    }

    // Init basket count
    updateBasketCount();

    // Add-to-basket buttons
    document.querySelectorAll(".add-to-basket").forEach(btn=>{
        btn.addEventListener("click", function(){
            const basket = getBasket();
            const item = {
                id: this.dataset.id,
                name: this.dataset.name,
                price: parseFloat(this.dataset.price),
                image: this.dataset.image,
                qty: 1
            };

            // check if already in basket
            const existing = basket.find(p => p.id === item.id);
            if(existing){
                existing.qty++;
            } else {
                basket.push(item);
            }

            saveBasket(basket);
            alert(item.name + " toegevoegd aan winkelmand!");
        });
    });

    // Search filter
    document.getElementById('search').addEventListener('keyup', function(){
        let filter = this.value.toLowerCase();
        document.querySelectorAll('#products > div').forEach(function(p){
            let name = p.querySelector('h3').textContent.toLowerCase();
            p.style.display = name.includes(filter) ? 'flex' : 'none';
        });
    });
</script>

</body>
</html>
