<?php
require_once 'config/database.php';
require_once 'auth_functions.php';

// Initialize $pdo from config.php
global $pdo;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Replace FILTER_SANITIZE_STRING with FILTER_SANITIZE_FULL_SPECIAL_CHARS
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (loginAdmin($username, $password, $pdo)) {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
   <style>
    body {
        font-family: Arial, sans-serif;
        max-width: 400px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff; /* light brown background */
        color: #1a1a1a; /* almost black text */
    }

    .error {
        color: #b00020; /* deep red for errors */
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        color: rgb(194, 156, 75); /* dark brown label text */
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        background-color: #fff; /* white input background */
        color: #1a1a1a;
    }

    button {
        padding: 10px 15px;
        background-color: rgb(194, 156, 75); /* medium brown button */
        color: #fff;
        border: none;
        cursor: pointer;
    }

    button:hover {
        background-color: rgb(194, 156, 75); /* darker brown on hover */
    }
</style>
</head>
<body>
    <h1>Admin Login</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>