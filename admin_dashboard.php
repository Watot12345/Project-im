<?php
require_once 'auth_functions.php';
requireAdminAccess();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff;
      color: rgb(194, 156, 75);
    }

    h1, h2 {
      color: rgb(194, 156, 75);
    }

    .dashboard-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 2rem;
      background-color: black;
      border-radius: 10px;
      margin-top: 40px;
      box-shadow: 0 4px 8px white;
    }

    ul {
      list-style-type: none;
      padding: 0;
    }

    ul li {
      margin-bottom: 1rem;
    }

    a {
      text-decoration: none;
      color: rgb(194, 156, 75);
      background-color: white;
      padding: 10px 20px;
      border-radius: 20px;
      display: inline-block;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    a:hover {
      background-color: rgb(194, 156, 75);
      color: white;
    }

    p {
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></h1>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <p>Associated User: <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></p>
    <?php endif; ?>

    <p>Permission Level: <?= ($_SESSION['permission_level'] ?? 0) == 2 ? 'Super Admin' : 'Admin' ?></p>

    <h2>Admin Actions</h2>
    <ul>
      <li><a href="manage_users.php">Manage Users</a></li>
      <li><a href="system_settings.php">System Settings</a></li>
      <?php if (isSuperAdmin()): ?>
        <li><a href="manage_admins.php">Manage Admins</a></li>
        <li><a href="system_settings.php">System Settings</a></li>
      <?php endif; ?>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </div>
</body>
</html>