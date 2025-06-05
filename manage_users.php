<?php
require_once 'auth_functions.php';
requireAdminAccess();
require_once 'config/database.php'; 
$database = new Database();
$pdo = $database->getConnection();

// Fetch users (now includes 'created_at')
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.business_name, u.email, MAX(login_time) AS created_at
    FROM users u
    LEFT JOIN login_logs l ON u.id = l.user_id
    GROUP BY u.id, u.name, u.business_name, u.email
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional: if you have a separate CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-left: 250px;
            padding: 20px;
        }
        h1 {
            color: rgb(194, 156, 75);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: black;
            color: rgb(194, 156, 75);
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions a {
            padding: 6px 12px;
            text-decoration: none;
            background-color: black;
            color: rgb(194, 156, 75);
            border-radius: 5px;
            margin-right: 5px;
            transition: 0.3s ease;
        }
        .actions a:hover {
            background-color: rgb(194, 156, 75);
            color: black;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            background-color: black;
            color: rgb(194, 156, 75);
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
        }
        .back-btn:hover {
            background-color: rgb(194, 156, 75);
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>
        <?php if (count($users) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Business Name</th>
                    <th>Email</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $user): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($user['name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($user['business_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                    <td>
                        <?= isset($user['created_at']) && !empty($user['created_at']) 
                            ? htmlspecialchars(date("Y-m-d", strtotime($user['created_at']))) 
                            : 'N/A' ?>
                    </td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a>
                        <br> <br> <br>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
        <a class="back-btn" href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>