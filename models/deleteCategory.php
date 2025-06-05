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
        if ($user->deleteCategory($id)) {
            echo "<script>
                alert('Category deleted successfully!');
                window.location.href = '../views/home.php';
            </script>";
        } else {
            echo "<div style='color: red; text-align: center; padding: 20px;'>Failed to delete category</div>";
            echo "<script>
                alert('Failed to delete category(DELETE YOUR SALES FIRST!!)');
                window.location.href = '../views/home.php';
            </script>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red; text-align: center; padding: 20px;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.href = '../views/home.php';
        </script>";
    }
} else {
    echo "<div style='color: red; text-align: center; padding: 20px;'>No category ID provided</div>";
    echo "<script>
        alert('No category ID provided');
        window.location.href = '../views/home.php';
    </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Category</title>
    <style>
        .error { color: red; padding: 10px; }
        .success { color: green; padding: 10px; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
</body>
</html>