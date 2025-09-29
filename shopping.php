<?php
include 'includes/database.php';
global $db;

// Haal alle producten uit de database
$result = mysqli_query($db, "SELECT * FROM shop_products");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Food Shop</title>
    <style>
        .product { border:1px solid #ccc; padding:10px; margin:10px; display:inline-block; width:200px; vertical-align:top; }
        img { width:180px; height:180px; object-fit:cover; }
    </style>
</head>
<body>
<h1>🛒 Food Shop</h1>

<!-- Zoekveld -->
<input type="text" id="search" placeholder="Zoek product...">
<div id="products">
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="product">
            <img src="<?php
            // Gebruik image direct; fallback als leeg
            echo !empty($row['image']) ? htmlspecialchars($row['image']) : 'uploads/default.jpg';
            ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">

            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <p>Portie: <?php echo !empty($row['gram']) ? htmlspecialchars($row['gram']) : 'n.v.t.'; ?></p>
            <strong>€<?php echo number_format($row['price'], 2, ',', '.'); ?></strong>
        </div>
    <?php endwhile; ?>
</div>

<script>
    // Eenvoudige zoekfunctie met JavaScript
    document.getElementById('search').addEventListener('keyup', function(){
        let filter = this.value.toLowerCase();
        document.querySelectorAll('.product').forEach(function(p){
            let name = p.querySelector('h3').textContent.toLowerCase();
            p.style.display = name.includes(filter) ? 'inline-block' : 'none';
        });
    });
</script>
</body>
</html>
