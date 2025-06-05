<?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();

$database = new Database();
$db = $database->getConnection();
$user = new user($db);

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        if ($user->deleteSale($id)) {
            echo "<script>
                alert('Sale deleted successfully!');
                window.location.href = '../views/home.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to delete sale(DELETE YOUR SALES FIRST!!)');
                window.location.href = '../views/home.php';
            </script>";
        }
    } catch (Exception $e) {
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.href = '../views/home.php';
        </script>";
    }
} else {
    echo "<script>
        alert('No sale ID provided');
        window.location.href = '../views/home.php';
    </script>";
}
?>