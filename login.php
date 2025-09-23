<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/** @var mysqli $db */
require_once 'includes/database.php';
session_start();

$login = false;


if (isset($_POST['submit'])) {

    $email = mysqli_escape_string($db, $_POST['email']);
    $password = mysqli_escape_string($db, $_POST['password']);

    if ($email == '') {
        $errors['email'] = 'Uw email is verplicht';
    }
    if ($password == '') {
        $errors['password'] = 'uw wachtwoord is verplicht';
    }
    if (empty($errors)) {
        $query = "
       SELECT 'email' FROM users WHERE `email` = '$email'
       ";
        $result = mysqli_query($db, $query)
        or die('Error: ' . mysqli_error($db) . 'with query ' . $query);

        if (mysqli_num_rows($result) == 1) {
            $query = "
        SELECT * FROM `users` WHERE `email` = '$email'
        ";
            $result = mysqli_query($db, $query)
            or die('Error: ' . mysqli_error($db) . 'with query ' . $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $users = $row;
            }

        } else {
            $errors['loginFailed'] = 'Login failed';
        }
        if (empty($errors)) {
            if (password_verify($password, $users['password']) == true) {
                $_SESSION['Login'] = true;
                $_SESSION['firstName'] = $users['first_name'];
                $_SESSION['lastName'] = $users['last_name'];
                $_SESSION['email'] = $users['email'];
                $_SESSION['admin_id'] = $users['admin_id'];
                $_SESSION['id'] = $users['id'];
                header('location: index.php');
                exit();
            } else {
                $errors['loginFailed'] = 'Uw logininformatie in niet correct';
            }
        }
    }
}


mysqli_close($db);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="src/style.css">
</head>
<body class="bg-[var(--background)] min-h-screen flex flex-col text-gray-800">
<nav class="flex items-center justify-between px-6 py-4 bg-white shadow">
    <a href="#"><img src="" alt="logo" class="h-10"></a>
    <div class="space-x-4 text-xl">
        <a href="#" class="hover:text-blue-600">⚙️</a>
        <a href="#" class="hover:text-blue-600">🔔</a>
    </div>
</nav>
<header class="text-center mt-12">
    <h1 class="text-3xl font-bold text-gray-900">Login</h1>
</header>
<main class="flex flex-1 items-center justify-center">
    <form action="" method="post"
          class="bg-white shadow-md rounded-2xl p-8 w-full max-w-md space-y-6">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
            <input id="email" type="email" name="email"
                   value="<?= htmlentities($email ?? '') ?>"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            <p class="text-sm text-red-600 mt-1">
                <?= $errors['email'] ?? '' ?>
            </p>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Wachtwoord</label>
            <input id="password" type="password" name="password"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            <p class="text-sm text-red-600 mt-1">
                <?= $errors['password'] ?? '' ?>
            </p>
            <p class="text-sm text-red-600 mt-1">
                <?= $errors['loginFailed'] ?? '' ?>
            </p>
        </div>
        <div>
            <button type="submit" name="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                Login
            </button>
        </div>
        <div class="text-center text-sm">
            <p class="text-gray-600">Nog geen account?</p>
            <a href="register.php" class="text-blue-600 hover:underline">Register</a>
        </div>
    </form>
</main>
<footer class="py-6 text-center text-gray-500 text-sm">
    <p>©Nutricoach</p>
</footer>
</body>
</html>
