<?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();

$database = new Database();
$db = $database->getConnection();
$user = new user($db);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        if ($user->delete($id)) {
            $_SESSION['success'] = "User deleted successfully";
            header("Location: ../views/home.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete user";
            header("Location: ../views/home.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../views/home.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No user ID provided";
    header("Location: ../views/home.php");
    exit();
}
?><?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();

$database = new Database();
$db = $database->getConnection();
$user = new user($db);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        if ($user->delete($id)) {
            $_SESSION['success'] = "User deleted successfully";
            header("Location: ../views/home.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete user";
            header("Location: ../views/home.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../views/home.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No user ID provided";
    header("Location: ../views/home.php");
    exit();
}
?>