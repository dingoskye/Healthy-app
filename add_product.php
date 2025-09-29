<?php
include 'includes/database.php';
global $db;

// Initialisatie
$api_photo_url = null;
$gram_suggestion = null;

// --- 1) Formulier POST verwerking ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_raw = str_replace(',', '.', trim($_POST['price'] ?? ''));

    if ($name === '') die("Vul een naam in.");
    if ($price_raw === '' || !is_numeric($price_raw)) die("Ongeldige prijs.");
    $price = (float)$price_raw;

    $use_api = isset($_POST['use_api_photo']) && $_POST['use_api_photo'] == 1;
    $uploaded_file = $_FILES['image']['tmp_name'] ?? null;
    $foto_url = null;
    $gram = null;

    // API foto gebruiken
    if ($use_api) {
        $foto_url = $_POST['api_photo_url'] ?? null;
        $gram = $_POST['api_gram'] ?? null;
    }

    // Eigen foto upload
    if (!$foto_url && $uploaded_file) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $ext = strtolower(preg_replace('/[^a-z0-9]/i', '', $ext));
        if ($ext === '') $ext = 'jpg';

        $uploads_dir = __DIR__ . '/uploads';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

        $filename = uniqid('prod_', true) . '.' . $ext;
        $target_path = $uploads_dir . '/' . $filename;

        if (!move_uploaded_file($tmp_name, $target_path)) die("Kon bestand niet uploaden.");
        $foto_url = 'uploads/' . $filename;
    }

    if (!$foto_url) die("Je moet een foto kiezen: API of eigen upload.");

    // Database insert
    $sql = "INSERT INTO shop_products (name, description, price, image, gram) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) die("DB prepare error: " . mysqli_error($db));
    mysqli_stmt_bind_param($stmt, "ssdss", $name, $description, $price, $foto_url, $gram);
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Product toegevoegd!</p>";
    } else {
        die("Fout bij opslaan in database: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
}

// --- 2) API suggestie ophalen bij formulier load ---
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['use_api_photo'])) {
    $name_for_api = $_POST['name'] ?? '';
    if ($name_for_api) {
        $apiUrl = "https://world.openfoodfacts.org/cgi/search.pl?search_terms=" . urlencode($name_for_api) . "&search_simple=1&action=process&json=1";
        $apiResponse = @file_get_contents($apiUrl);
        if ($apiResponse !== false) {
            $data = json_decode($apiResponse, true);
            $bestMatch = null;
            if (!empty($data['products'])) {
                foreach ($data['products'] as $p) {
                    if (strcasecmp($p['product_name'] ?? '', $name_for_api) === 0) {
                        $bestMatch = $p;
                        break;
                    }
                }
            }
            $product = $bestMatch ?? $data['products'][0] ?? null;
            if ($product) {
                $api_photo_url = $product['image_url'] ?? null;
                $gram_suggestion = $product['serving_size'] ?? null;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nieuw product toevoegen</title>
    <style>
        img { width: 180px; height: 180px; object-fit: cover; border:1px solid #ccc; margin:5px 0; }
    </style>
</head>
<body>
<h1>Nieuw product toevoegen</h1>
<form method="POST" enctype="multipart/form-data">
    Naam: <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required><br><br>
    Beschrijving: <textarea name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea><br><br>
    Prijs (€): <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required><br><br>

    <!-- API Suggestie -->
    <?php if ($api_photo_url): ?>
        <div>
            <p>Suggestie van API (portie: <?php echo htmlspecialchars($gram_suggestion ?? 'n.v.t.'); ?>):</p>
            <img src="<?php echo htmlspecialchars($api_photo_url); ?>" alt="API Suggestie"><br>
            <input type="checkbox" name="use_api_photo" value="1" checked> Gebruik deze foto<br>
            <input type="hidden" name="api_photo_url" value="<?php echo htmlspecialchars($api_photo_url); ?>">
            <input type="hidden" name="api_gram" value="<?php echo htmlspecialchars($gram_suggestion); ?>">
        </div><br>
    <?php endif; ?>

    <!-- Eigen upload -->
    Of upload je eigen foto: <input type="file" name="image" accept="image/*"><br><br>

    <button type="submit">Toevoegen</button>
</form>
</body>
</html>
