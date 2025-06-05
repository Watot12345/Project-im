<?php
require_once 'config/database.php';
define('SESSION_TIMEOUT', 1800); // 1800 seconds = 30 minutes
function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Use secure parameters if needed
        session_start();
    }
}

function loginAdmin($username, $password, $pdo = null) {
    if ($pdo === null) {
        $database = new Database();
       $pdo = $database->getConnection();
    }

    sleep(1); // Throttle brute-force attempts

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        secure_session_start();

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['permission_level'] = $admin['permission_level'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // If admin is associated with a user account
        if (!empty($admin['user_id'])) {
            $user = getUserById($admin['user_id'], $pdo);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['business_id'] = $user['business_id'];
            }
        }

        return true;
    }

    return false;
}

function getUserById($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT id, name, email, business_id FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    return $stmt->fetch();
}

function isAdminLoggedIn() {
    secure_session_start();
    
    // Check if all session variables are set
    if (!isset($_SESSION['admin_id'], 
               $_SESSION['admin_username'], 
               $_SESSION['login_ip'], 
               $_SESSION['login_user_agent'],
               $_SESSION['last_activity'])) {
        return false;
    }
    
    // Check inactivity timeout
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        logoutAdmin();
        return false;
    }
    
    // Check if IP or user agent changed
    if ($_SERVER['REMOTE_ADDR'] != $_SESSION['login_ip'] || 
        $_SERVER['HTTP_USER_AGENT'] != $_SESSION['login_user_agent']) {
        logoutAdmin();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

function logoutAdmin() {
    secure_session_start();
    
    // Unset all session values
    $_SESSION = array();
    
    // Get session parameters
    $params = session_get_cookie_params();
    
    // Delete the actual cookie
    setcookie(session_name(),
              '', 
              time() - 42000,
              $params["path"],
              $params["domain"],
              $params["secure"],
              $params["httponly"]
    );
    
    // Destroy session
    session_destroy();
}

function requireAdminAccess() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php?error=session_expired');
        exit();
    }
}

function isSuperAdmin() {
    return (isAdminLoggedIn() && $_SESSION['permission_level'] >= 2);
}
?>