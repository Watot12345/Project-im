<?php
require_once 'auth_functions.php';
requireAdminAccess();
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_GET['id'])) {
    die("User ID is missing.");
}

$id = $_GET['id'];

// Optional: add checks or log before deletion
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header("Location: manage_users.php");
exit;
?>