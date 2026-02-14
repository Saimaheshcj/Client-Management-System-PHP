<?php
require_once 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert into users table
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, 'manager')
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT)
        ]);
        $user_id = $pdo->lastInsertId();

        // Insert into managers table
        $stmt = $pdo->prepare("
            INSERT INTO managers (user_id, department)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $user_id,
            $_POST['department']
        ]);

        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Manager added successfully.";
        header("Location: managers.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error adding manager: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1>Add New Manager</h1>
    <a href="managers.php" class="btn-secondary">Back to Managers</a>
</div>

<div class="content-card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Manager</button>
            <a href="managers.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 