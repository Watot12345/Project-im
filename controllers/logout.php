<?php
session_start();
session_destroy();
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

header('Location: ../index.php');
echo json_encode(['status' => 'success']);
?>
