<?php
$showSignup = isset($_GET['action']) && $_GET['action'] === 'signup';
?>
<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Stocking Go! Landpage</title>
      <link rel="stylesheet" href="assets/css/styled.css">
      <link rel="shortcut icon" href="assets/images/site-logo.png" type="image/x-icon">
      <script src="assets/js/script.js" defer></script>
</head>

<body>
      <!-- Navigation -->
      <nav class="nav-land">
            <a href="views/login_signup.php" class="login-link">Login</a>
      </nav>

      <div class="container-land">

            <!-- Left Image -->
            <div class="image-sections">
                  <img src="assets/images/lanpage_pic.png" alt="Landing Image" class="image">
            </div>

            <!-- Right Content -->
            <div class="content-sections">
                  <h1 class="title">Stocking Go!</h1>
                  <p class="description">Your next-level Stocking Management!</p>
                  <a href="views/login_signup.php?action=signup" class="cta-button">Sign up Here!</a>
            </div>

      </div>
</body>

</html>
