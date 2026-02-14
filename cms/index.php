<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Prepare login query based on user type
    switch ($user_type) {
        case 'admin':
            $stmt = $pdo->prepare("SELECT admin_id, username, password FROM admin WHERE username = ?");
            $params = [$username];
            break;
        case 'manager':
            // Managers stored in users + managers tables
            $stmt = $pdo->prepare(
                "SELECT m.manager_id, u.user_id, u.name, u.email, u.password
                FROM users u
                JOIN managers m ON u.user_id = m.user_id
                WHERE u.email = ?"
            );
            $params = [$username];
            break;
        case 'client':
            // Clients stored in users + clients tables
            $stmt = $pdo->prepare(
                "SELECT c.client_id, u.user_id, u.name, u.email, u.password
                FROM users u
                JOIN clients c ON u.user_id = c.user_id
                WHERE u.email = ?"
            );
            $params = [$username];
            break;
        default:
            $stmt = null;
    }
    // Execute and fetch user record
    if ($stmt) {
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verify password
    if ($user) {
        $valid = false;
        if ($user_type === 'admin') {
            // Admin passwords are stored in plain text
            if ($password === $user['password']) {
                $valid = true;
            }
        } else {
            // Manager and client passwords are hashed
            if (password_verify($password, $user['password'])) {
                $valid = true;
            }
        }
        if ($valid) {
            // Set session variables
            $_SESSION['user_id'] = $user[$user_type . '_id'];
            $_SESSION['user_type'] = $user_type;
            // Use appropriate display name
            $_SESSION['username'] = ($user_type === 'admin') ? $user['username'] : $user['name'];
            // Redirect to dashboard
            header("Location: " . $user_type . "/dashboard.php");
            exit();
        }
    }
    $error = "Invalid credentials";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management System - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Client Management System</h1>
        <form method="POST" action="">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="user_type">User Type:</label>
                <select name="user_type" id="user_type" required>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="client">Client</option>
                </select>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html> 