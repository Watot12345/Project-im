<?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();
$database = new Database();     
$db = $database->getConnection();
$user = new user($db);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
       
        
        if ($user->edit($id, $name)) {
            echo "<script>alert('Edit successful!'); setTimeout(function(){window.location.href = '../views/home.php'; }, 500);</script>";
        } else {
            echo "Edit failed!";
        }
    }
} else {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $user->members();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link rel="stylesheet" href="../assets/css/styled.css">
</head>
<body>
    <div class="edit member">
        <h1>Edit Member</h1>
        <form method="POST" action="edit.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <input type="text" name="name" placeholder="Name" value="" >
            <button type="submit" name="edit">Edit</button>
        </form>
    </div>
</body>
</html>