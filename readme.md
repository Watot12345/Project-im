# Admin Panel - User Management System

## 📋 Overview
This project provides a basic admin panel where an administrator can **view**, **edit**, and **delete** user accounts.

The system uses:
- **PHP** with **PDO** for secure database interactions
- **HTML/CSS** for the frontend
- **MySQL** for the database backend

---

## 🔐 Admin Login Credentials

Use the following credentials to log in as an administrator:

- **Username**: `admin`
- **Password**: `password`

---

## 📁 Files Included

- `manage_users.php` – Admin dashboard to view all users
- `edit_user.php` – Edit user info
- `delete_user.php` – Delete user from database
- `auth_functions.php` – Handles access control
- `config/database.php` – PDO-based database connection
- `styles.css` – Custom styles (optional)

---

## 🛠️ Requirements

- PHP 7.4 or higher
- MySQL or MariaDB
- Local server like XAMPP, KSWEB (Android), or Apache + MySQL

---

## 🚀 Usage

1. Import the SQL database provided (if available).
2. Place the project in your `htdocs` or local server directory.
3. Open the browser and go to `http://localhost/your-folder-name/`
4. Login using the **admin credentials** above.
5. Manage users via the dashboard.

---

## ⚠️ Security Note

For demonstration purposes, the default admin username and password are hardcoded.  
Make sure to:
- Change the default credentials in a production environment
- Use password hashing (e.g., `password_hash`) for real security