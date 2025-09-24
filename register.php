<?php
require_once 'includes/database.php';

if (isset($_POST['submit'])) {
    // Read and sanitize POST values
    $firstName   = trim($_POST['firstName'] ?? '');
    $lastName    = trim($_POST['lastName'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? ''); // expected format: YYYY-MM-DD
    $sex         = trim($_POST['sex'] ?? '');
    $height      = trim($_POST['height'] ?? ''); // numeric (cm)
    $weight      = trim($_POST['weight'] ?? ''); // numeric (kg)

    $errors = [];

    // Validation
    if ($firstName === '') {
        $errors['firstName'] = 'First name is required.';
    }
    if ($lastName === '') {
        $errors['lastName'] = 'Last name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }
    if ($password === '' || strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }
    // Validate date (simple)
    if ($dateOfBirth === '' || !\DateTime::createFromFormat('Y-m-d', $dateOfBirth)) {
        $errors['dateOfBirth'] = 'Date of birth is required in YYYY-MM-DD format.';
    }
    if ($sex === '') {
        $errors['sex'] = 'Answer is required.';
    }
    if ($height === '' || !is_numeric($height)) {
        $errors['height'] = 'Height (cm) is required and must be numeric.';
    }
    if ($weight === '' || !is_numeric($weight)) {
        $errors['weight'] = 'Weight (kg) is required and must be numeric.';
    }

    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Map to DB column names from `SQL-bestanden/prototype_nutribot.sql`
        $query = "INSERT INTO users (first_name, last_name, email, password, date_of_birth, sex, height_cm, weight_kg) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt === false) {
            $errors['database'] = 'Failed to prepare statement: ' . $db->error;
        } else {
            // Bind parameters: 6 strings (s) and 2 doubles (d) for decimal columns
            $heightFloat = (float) $height;
            $weightFloat = (float) $weight;
            $stmt->bind_param('ssssssdd', $firstName, $lastName, $email, $hashedPassword, $dateOfBirth, $sex, $heightFloat, $weightFloat);

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: login.php');
                exit;
            } else {
                $errors['database'] = 'Something went wrong while saving the user: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
}
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

<div class="flex-grow flex items-center justify-center px-4">
    <main class="w-full max-w-2xl bg-white shadow-md rounded-lg p-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Create an account</h1>

        <form action="register.php" method="post" class="space-y-6" novalidate>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                    <input id="firstName" name="firstName" type="text" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>

                <div>
                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                    <input id="lastName" name="lastName" type="text" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" name="email" type="email" required
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="dateOfBirth" class="block text-sm font-medium text-gray-700 mb-1">Date of birth</label>
                    <input id="dateOfBirth" name="dateOfBirth" type="date" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>

                <div>
                    <label for="sex" class="block text-sm font-medium text-gray-700 mb-1">Sex</label>
                    <select id="sex" name="sex" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Choose...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other / Prefer not to say</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                    <input id="height" name="height" type="number" step="0.01" min="0" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>

                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input id="weight" name="weight" type="number" step="0.01" min="0" required
                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>

            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">Already have an account? <a href="login.php" class="text-indigo-600 hover:underline">Sign in</a></div>
                <button type="submit" name="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    Register
                </button>
            </div>
        </form>
    </main>
</div>


</body>
</html>


