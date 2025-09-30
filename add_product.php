
<?php
include 'includes/database.php';
global $db;

$api_photo_url   = null;
$gram_suggestion = null;
$error_message   = null;
$success_message = null;

// Opslaan bij POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name         = trim($_POST['name'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $price_raw    = str_replace(',', '.', trim($_POST['price'] ?? ''));
    $photo_choice = $_POST['photo_choice'] ?? '';

    if ($name === '') {
        $error_message = "Vul een naam in.";
    } elseif ($price_raw === '' || !is_numeric($price_raw)) {
        $error_message = "Ongeldige prijs.";
    } elseif ($photo_choice !== 'api' && $photo_choice !== 'upload') {
        $error_message = "Kies een foto-optie.";
    } else {
        $price    = (float)$price_raw;
        $foto_url = null;
        $gram     = null;

        if ($photo_choice === 'api') {
            $foto_url = $_POST['api_photo_url'] ?? null;
            $gram     = $_POST['api_gram'] ?? null;
            if (!$foto_url) {
                $error_message = "Geen API-foto beschikbaar.";
            }
        } elseif ($photo_choice === 'upload') {
            if (!isset($_FILES['image']['tmp_name']) || $_FILES['image']['tmp_name'] === '') {
                $error_message = "Upload een foto.";
            } else {
                $tmp_name    = $_FILES['image']['tmp_name'];
                $ext         = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if ($ext === '') $ext = 'jpg';
                $uploads_dir = __DIR__ . '/uploads';
                if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);
                $filename    = uniqid('prod_', true) . '.' . $ext;
                $target_path = $uploads_dir . '/' . $filename;
                if (!move_uploaded_file($tmp_name, $target_path)) {
                    $error_message = "Kon bestand niet uploaden.";
                } else {
                    $foto_url = 'uploads/' . $filename;
                }
            }
        }

        // ✅ Insert uitvoeren
        if (!$error_message) {
            $sql  = "INSERT INTO shop_products (name, description, price, image, gram) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql);

            if (!$stmt) {
                $error_message = "DB prepare error: " . mysqli_error($db);
            } else {
                // 'ssdsi' = string, string, double, string, integer
                mysqli_stmt_bind_param($stmt, "ssdsi", $name, $description, $price, $foto_url, $gram);

                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "✅ Product succesvol toegevoegd!";
                } else {
                    $error_message = "Fout bij opslaan: " . mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);
            }
        }
    } // ✅ sluit de else { van regel 21
}

// API suggestie ophalen
$name_for_api = $_GET['name'] ?? ($_POST['name'] ?? '');
if ($name_for_api) {
    $apiUrl     = "https://world.openfoodfacts.org/cgi/search.pl?search_terms=" . urlencode($name_for_api) . "&search_simple=1&action=process&json=1";
    $apiResponse = @file_get_contents($apiUrl);
    if ($apiResponse !== false) {
        $data      = json_decode($apiResponse, true);
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
            $api_photo_url   = $product['image_url'] ?? null;
            $gram_suggestion = $product['serving_size'] ?? null;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw product toevoegen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleChoice() {
            const val = document.querySelector('input[name="photo_choice"]:checked')?.value;
            document.getElementById('api_block').style.display = (val === 'api') ? 'flex' : 'none';
            document.getElementById('upload_block').style.display = (val === 'upload') ? 'flex' : 'none';
        }
        function showToast() {
            const t = document.getElementById('toast');
            t.classList.add('opacity-100');
            setTimeout(() => t.classList.remove('opacity-100'), 3000);
        }
    </script>
</head>
<body class="bg-[#FAF3DD] flex flex-col min-h-screen text-[#264653]">

<!-- HEADER -->
<header class="bg-[#4A7C59] text-[#FAF3DD] p-6 text-center shadow-md">
    <h1 class="text-3xl font-bold">Nieuw product toevoegen</h1>
</header>

<main class="flex-1 max-w-4xl mx-auto px-4 py-6 space-y-4">

    <!-- Zoekformulier -->
    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="name" value="<?php echo htmlspecialchars($name_for_api); ?>"
               class="flex-1 p-2 rounded border-2 border-[#C8D5B9] focus:ring-2 focus:ring-[#68B0AB]"
               placeholder="Productnaam voor API-zoek" required>
        <button type="submit" class="bg-[#68B0AB] hover:bg-[#8FC0A9] text-[#FAF3DD] px-4 py-2 rounded font-semibold">Zoek</button>
    </form>

    <!-- Foutmelding -->
    <?php if ($error_message): ?>
        <div class="p-3 rounded font-semibold text-[#FAF3DD]" style="background-color:#e63946;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Formulier -->
    <form method="POST" enctype="multipart/form-data" class="bg-[#C8D5B9] p-6 rounded-xl shadow-md space-y-4">

        <input type="hidden" name="name" value="<?php echo htmlspecialchars($name_for_api); ?>">

        <label class="block font-semibold">Beschrijving:</label>
        <textarea name="description" class="w-full p-2 rounded border border-[#8FC0A9]"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

        <label class="block font-semibold">Prijs (€):</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
               class="w-full p-2 rounded border border-[#8FC0A9]">

        <h3 class="font-semibold">Kies een foto-optie:</h3>
        <div class="flex gap-4 mb-2">
            <label class="flex items-center gap-2"><input type="radio" name="photo_choice" value="api" onclick="toggleChoice()" <?php if (($_POST['photo_choice'] ?? '') === 'api') echo 'checked'; ?>> API-foto</label>
            <label class="flex items-center gap-2"><input type="radio" name="photo_choice" value="upload" onclick="toggleChoice()" <?php if (($_POST['photo_choice'] ?? '') === 'upload') echo 'checked'; ?>> Eigen foto</label>
        </div>

        <!-- Foto keuzes -->
        <div class="flex gap-4 justify-start">
            <!-- API Suggestie -->
            <div id="api_block" class="flex flex-col items-center justify-center p-2 bg-[#8FC0A9] rounded"
                 style="width:20rem; height:14rem; display: <?php echo (($_POST['photo_choice'] ?? '') === 'api') ? 'flex' : 'none'; ?>;">
                <?php if ($api_photo_url): ?>
                    <p class="text-sm text-[#264653] text-center">Suggestie (portie: <?php echo htmlspecialchars($gram_suggestion ?? 'n.v.t.'); ?>)</p>
                    <img src="<?php echo htmlspecialchars($api_photo_url); ?>" class="w-48 h-48 object-cover border-2 border-[#68B0AB] rounded mt-1">
                    <input type="hidden" name="api_photo_url" value="<?php echo htmlspecialchars($api_photo_url); ?>">
                    <input type="hidden" name="api_gram" value="<?php echo htmlspecialchars($gram_suggestion); ?>">
                <?php else: ?>
                    <p class="text-sm text-[#264653] text-center">Geen API-suggestie gevonden.</p>
                <?php endif; ?>
            </div>

            <!-- Upload -->
            <div id="upload_block" class="flex flex-col items-center justify-center p-2 bg-[#8FC0A9] rounded"
                 style="width:20rem; height:14rem; display: <?php echo (($_POST['photo_choice'] ?? '') === 'upload') ? 'flex' : 'none'; ?>;">
                <span>Upload je eigen foto:</span>
                <input type="file" name="image" accept="image/*" class="mt-2">
            </div>
        </div>

        <button type="submit" class="w-full bg-[#68B0AB] hover:bg-[#8FC0A9] text-[#FAF3DD] font-semibold px-4 py-2 rounded">Toevoegen</button>
    </form>

    <!-- Succesmelding -->
    <?php if ($success_message): ?>
        <div id="toast" class="fixed top-6 right-6 px-4 py-2 rounded shadow-lg text-[#FAF3DD] opacity-0 transition-opacity duration-500"
             style="background-color:#264653;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <script>showToast();</script>
    <?php endif; ?>

</main>

<footer class="bg-[#264653] text-[#FAF3DD] p-6 text-center mt-auto">
    &copy; <?php echo date('Y'); ?> Food Shop
</footer>

</body>
</html>
