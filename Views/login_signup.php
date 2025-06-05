<?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();

$database = new Database();
$db = $database->getConnection();

$user = new user($db);

$showSignup = isset($_GET['action']) && $_GET['action'] === 'signup';
// ...existing code...
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Capture form data for registration
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $bussname = isset($_POST['business_name']) ? trim($_POST['business_name']) : '';
        $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';

        // Call the register method and get the result
        $registrationResult = $user->register($name, $password, $email, $bussname, $confirm);

        if (is_array($registrationResult)) {
            // Display validation errors
            foreach ($registrationResult as $error) {
                echo "<p style='color:red; text-align: center; padding: 20px;'>$error</p>";
            }
        } elseif ($registrationResult) {
            // If registration is successful
            echo "<p style='color:green; text-align: center; padding: 20px; position: absolute;'>Registration successful!</p>";
        } else {
            // If registration fails for some reason (even after validation)
            echo "<p style='color:red; text-align: center; padding: 20px; position: absolute;'>Registration failed. Please try again.</p>";
        }
    }

    // Handle Login
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Check if email and password are provided
        if (empty($email) || empty($password)) {
            echo "<script>alert('Please fill in all fields');</script>";
        } else {
            // Attempt to log the user in
            $loginResult = $user->login($email, $password);

            if ($loginResult['success']) {
                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $loginResult['user_id'];
                $_SESSION['email'] = $loginResult['user_email'];
                $_SESSION['name'] = $loginResult['user_name'];
                $_SESSION['business_name'] = $loginResult['business_name'];

                // Redirect to members page after successful login
                header("Location: ../views/home.php");
                exit(); // Make sure to exit after the redirection
            } else {
                // Display error message
                echo "<script>alert('" . $loginResult['message'] . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title> Stocking GO! Login & Sign Up </title>
      <link rel="stylesheet" href="../assets/css/styled.css">
      <script>           
        function showSignUp() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('signupForm').style.display = 'block';
        }
        function showLogin() {
            document.getElementById('signupForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        }
      </script>
      <link rel="shortcut icon" href="../assets/images/site-logo.png" type="image/x-icon">
</head>

<body>
      <div class="form-wrapper">
      <form action="login_signup.php" method="POST" id="loginForm">
      <div class="form-container" id="loginForm">
            <h2>Login</h2>
            <div class="form-group">
                  <label for="loginEmail">Email</label>
                  <input type="email" id="loginEmail" name="email"placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                  <label for="loginPassword">Password</label>
                  <input type="password" id="loginPassword" name="password"placeholder="Enter your password" required>
            </div>
            <button class="btn" type="submit" name="login">Login</button>
            <div class="toggle-link">
                  Don't have an account? <a href="javascript:void(0)" onclick="showSignUp()">Sign Up</a>
            </div>
    <div class="toggle-link">
                  Login as an Admin? <a href="../admin.php">Admin Login</a>
            </div>
      </div>
  </form>

  <form action="login_signup.php" method="POST" id="signupForm" style="display: none;">
  <div class="form-container">
            <h2>Sign Up</h2>
            <div class="form-group">
                  <label for="signupName">Full Name</label>
                  <input type="text" id="signupName" name="name"placeholder="Enter your full name">
            </div>
            <div class="form-group">
                  <label for="signupEmail">Email</label>
                  <input type="email" id="signupEmail" name="email"placeholder="Enter your email" >
            </div>
            <div class="form-group">
                  <label for="businessName">Business Name</label>
                  <input type="text" id="businessName" name="business_name"placeholder="Enter your business name" >
            </div>
            <div class="form-group">
                  <label for="signupPassword">Password</label>
                  <input type="password" name="password" id="signupPassword" placeholder="Create a password" >
            </div>
            <div class="form-group">
                  <label for="retypePassword">Retype Password</label>
                  <input type="password" id="retypePassword" name="confirm" placeholder="Retype your password">
            </div>
            <button class="btn" type="submit" name="register">Sign Up</button>
            <div class="toggle-link">
                  Already have an account?<a href="javascript:void(0)" onclick="showLogin()">Login</a>
            </div>
      </div>
  </form>
      </div>
</body>

</html>
