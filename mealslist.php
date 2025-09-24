<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'includes/database.php';

$errors = [];

// GEEN login-check tijdens testen
// if (!isset($_SESSION['login'])) { header('Location: login.php'); exit(); }

if (isset($_POST['submit'])) {
    // tijdens testen: vaste user_id
    $userId = 1;

    // Form velden
    $mealType  = $_POST['meal_type'];
    // datetime-local => "YYYY-MM-DDTHH:MM" → maak "YYYY-MM-DD HH:MM:SS"
    $eatenAtIn = $_POST['eaten_at'];
    $eatenAt   = !empty($eatenAtIn) ? str_replace('T', ' ', $eatenAtIn) . ':00' : '';

    // Macro’s (laat leeg toe → zet op 0 om simpel te blijven)
    $proteinG  = $_POST['protein_g'] === '' ? 0 : $_POST['protein_g'];
    $carbsG    = $_POST['carbs_g']   === '' ? 0 : $_POST['carbs_g'];
    $fatG      = $_POST['fat_g']     === '' ? 0 : $_POST['fat_g'];
    $fiberG    = $_POST['fiber_g']   === '' ? 0 : $_POST['fiber_g'];

    $notes     = $_POST['notes'];
    $dish      = $_POST['dish'];
    $createdAt = date('Y-m-d H:i:s');

    // Validatie (zelfde eenvoud als je voorbeeld)
    if (empty($mealType)) {
        $errors['meal_type'] = "Meal type cannot be empty";
    }
    if (empty($eatenAt)) {
        $errors['eaten_at'] = "Eaten at cannot be empty";
    }
    if (empty($dish)) {
        $errors['dish'] = "Dish cannot be empty";
    }

    if (empty($errors)) {
        $query = "
            INSERT INTO meals
                (user_id, meal_type, eaten_at, protein_g, carbs_g, fat_g, fiber_g, notes, dish, created_at)
            VALUES
                ($userId, '$mealType', '$eatenAt', $proteinG, $carbsG, $fatG, $fiberG, '$notes', '$dish', '$createdAt')
        ";
        $result = mysqli_query($db, $query);

        if ($result) {
            header('Location: meals_index.php');
            exit();
        } else {
            $errors['db'] = "Error inserting into meals table.";
        }
    }
}
?>

<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/style.css">
    <title>Meal toevoegen</title>
</head>
<body>
<header>
    <div class="header-container-contact">
        <h1>Meal toevoegen</h1>
        <p>Log een maaltijd met macro’s.</p>
    </div>
</header>

<main>
    <form action="" method="post" class="contact-form">
        <input type="hidden" name="user_id" value="1">
        <div class="main-content-container">

            <div class="contact-field">
                <div class="contact-content">
                    <label for="meal_type">Meal type</label>
                    <input type="text" id="meal_type" name="meal_type" value="<?= htmlentities($_POST['meal_type'] ?? '') ?>">
                    <span class="error"><?= $errors['meal_type'] ?? '' ?></span>
                </div>
                <div class="contact-content">
                    <label for="eaten_at">Eaten at</label>
                    <input type="datetime-local" id="eaten_at" name="eaten_at" value="<?= htmlentities($_POST['eaten_at'] ?? '') ?>">
                    <span class="error"><?= $errors['eaten_at'] ?? '' ?></span>
                </div>
            </div>

            <div class="contact-field">
                <div class="contact-content">
                    <label for="protein_g">Protein (g)</label>
                    <input type="number" step="0.1" id="protein_g" name="protein_g" value="<?= htmlentities($_POST['protein_g'] ?? '') ?>">
                </div>
                <div class="contact-content">
                    <label for="carbs_g">Carbs (g)</label>
                    <input type="number" step="0.1" id="carbs_g" name="carbs_g" value="<?= htmlentities($_POST['carbs_g'] ?? '') ?>">
                </div>
            </div>

            <div class="contact-field">
                <div class="contact-content">
                    <label for="fat_g">Fat (g)</label>
                    <input type="number" step="0.1" id="fat_g" name="fat_g" value="<?= htmlentities($_POST['fat_g'] ?? '') ?>">
                </div>
                <div class="contact-content">
                    <label for="fiber_g">Fiber (g)</label>
                    <input type="number" step="0.1" id="fiber_g" name="fiber_g" value="<?= htmlentities($_POST['fiber_g'] ?? '') ?>">
                </div>
            </div>

            <div class="contact-content">
                <label for="dish">Dish</label>
                <input type="text" id="dish" name="dish" value="<?= htmlentities($_POST['dish'] ?? '') ?>">
                <span class="error"><?= $errors['dish'] ?? '' ?></span>
            </div>

            <div class="contact-content">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"><?= htmlentities($_POST['notes'] ?? '') ?></textarea>
            </div>

            <?php if (!empty($errors['db'])): ?>
                <p class="error"><?= $errors['db'] ?></p>
            <?php endif; ?>

            <div class="contact-field">
                <button type="submit" name="submit">Opslaan</button>
            </div>
        </div>
    </form>
</main>
</body>
</html>
