<?php
// basket.php

//Function for a logged in user to get access
session_start();

// Check if the visitor is logged in
if (!isset($_SESSION['Login'])) {  // **GET**: Check if user is logged in (session variable)
    // Redirect if not logged in
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelmand</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FAF3DD] text-[#264653] min-h-screen flex flex-col">

<!-- HEADER -->
<header class="bg-[#264653] text-[#eec584] p-6 text-center shadow-md">
    <h1 class="text-3xl font-bold">🛒 Jouw winkelmand</h1>
</header>

<!-- MAIN -->
<main class="flex-1 max-w-4xl mx-auto px-4 py-6 space-y-6">

    <!-- Mand items -->
    <div id="basket-items" class="space-y-4"></div>

    <!-- Totaal -->
    <div id="total-box" class="hidden p-4 bg-[#C8D5B9] rounded-xl shadow-md flex justify-between items-center">
        <span class="text-lg font-bold">Totaal:</span>
        <span class="text-xl font-bold text-[#00916E]">€<span id="total">0.00</span></span>
    </div>

    <!-- Afrekenen knop -->
    <button id="checkout"
            class="w-full font-bold py-3 px-4 rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
        Afrekenen
    </button>

    <!-- Terug naar shop knop (verborgen, pas zichtbaar na afrekenen) -->
    <a href="shopping.php" id="back-to-shop"
       class="hidden w-full text-center bg-[#264653] text-[#FAF3DD] font-bold py-3 px-4 rounded-xl transition hover:bg-[#4A7C59] mt-6">
        ← Terug naar de shop
    </a>

</main>

<!-- FOOTER -->
<footer class="bg-[#264653] text-[#FAF3DD] p-6 text-center mt-auto">
    &copy; <?php echo date("Y"); ?> Food Shop
</footer>

<!-- SCRIPT -->
<script>
    function getBasket() {
        return JSON.parse(localStorage.getItem("basket") || "[]");
    }

    function saveBasket(basket) {
        localStorage.setItem("basket", JSON.stringify(basket));
    }

    function changeQty(index, delta) {
        let basket = getBasket();
        basket[index].qty += delta;
        if (basket[index].qty <= 0) basket.splice(index, 1);
        saveBasket(basket);
        renderBasket();
    }

    function removeItem(index) {
        let basket = getBasket();
        basket.splice(index, 1);
        saveBasket(basket);
        renderBasket();
    }

    function renderBasket() {
        const basket = getBasket();
        const container = document.getElementById("basket-items");
        const checkoutBtn = document.getElementById("checkout");
        container.innerHTML = "";
        let total = 0;

        if (basket.length === 0) {
            container.innerHTML = "<p class='text-center text-lg'>Winkelmand is leeg.</p>";
            document.getElementById("total-box").style.display = "none";
            checkoutBtn.disabled = true;
            checkoutBtn.classList.remove("bg-[#68B0AB]","hover:bg-[#8FC0A9]","text-white");
            checkoutBtn.classList.add("bg-gray-400","text-gray-700");
            return;
        } else {
            document.getElementById("total-box").style.display = "flex";
            checkoutBtn.disabled = false;
            checkoutBtn.classList.add("bg-[#68B0AB]","hover:bg-[#8FC0A9]","text-white");
            checkoutBtn.classList.remove("bg-gray-400","text-gray-700");
        }

        basket.forEach((item, index) => {
            const subtotal = item.qty * item.price;
            total += subtotal;
            container.innerHTML += `
          <div class="flex items-center justify-between bg-[#C8D5B9] p-4 rounded-xl shadow-md">
            <div class="flex items-center gap-4">
              <img src="${item.image}" alt="${item.name}" class="w-20 h-20 object-cover rounded">
              <div>
                <h3 class="font-bold">${item.name}</h3>
                <p>€${item.price.toFixed(2)} x ${item.qty} = €${subtotal.toFixed(2)}</p>
              </div>
            </div>
            <div class="flex gap-2">
              <button onclick="changeQty(${index}, -1)" class="bg-red-500 text-white px-3 py-1 rounded">-</button>
              <button onclick="changeQty(${index}, 1)" class="bg-green-500 text-white px-3 py-1 rounded">+</button>
              <button onclick="removeItem(${index})" class="bg-gray-700 text-white px-3 py-1 rounded">🗑</button>
            </div>
          </div>
        `;
        });

        document.getElementById("total").textContent = total.toFixed(2);
    }

    document.getElementById("checkout").addEventListener("click", function() {
        localStorage.removeItem("basket");

        // Toon succesbericht met marge (afstand) onderaan
        document.getElementById("basket-items").innerHTML =
            "<p class='text-center text-xl font-bold text-green-700 mb-6'>✅ Bestelling is gelukt, dankjewel voor het shoppen met ons!</p>";

        // Verberg totaal en afrekenen-knop
        document.getElementById("total-box").style.display = "none";
        this.style.display = "none";

        // Toon de terug naar shop knop
        document.getElementById("back-to-shop").classList.remove("hidden");
    });

    renderBasket();
</script>

</body>
</html>
