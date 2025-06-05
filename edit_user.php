<?php
require_once 'auth_functions.php';
requireAdminAccess();
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

// Fetch user data
if (!isset($_GET['id'])) {
    die("User ID is missing.");
}
$id = $_GET['id'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $business_name = $_POST['business_name'] ?? '';
    $email = $_POST['email'] ?? '';

    $stmt = $pdo->prepare("UPDATE users SET name = ?, business_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $business_name, $email, $id]);

    header("Location: manage_users.php");
    exit;
}

// Fetch user details for form
$stmt = $pdo->prepare("SELECT name, business_name, email FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        body { font-family: Arial; padding: 20px; background-color: #f4f4f4; }
        form { background: white; padding: 20px; border-radius: 10px; max-width: 500px; margin: auto; }
        input[type=text], input[type=email] {
            width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px;
        }
        button { background: black; color: rgb(194, 156, 75); border: none; padding: 10px 20px; border-radius: 5px; }
        button:hover { background: rgb(194, 156, 75); color: black; }
        a { text-decoration: none; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>

<h2>Edit User</h2>
<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

    <label>Business Name:</label>
    <input type="text" name="business_name" value="<?= htmlspecialchars($user['business_name']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <button type="submit">Update</button>
    <a href="manage_users.php">Cancel</a>
</form>

</body>
</html>