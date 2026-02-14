<?php
require_once 'includes/header.php';

// Check if manager ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No manager ID provided.";
    header("Location: managers.php");
    exit();
}

$manager_id = $_GET['id'];

// Get manager and user information
$stmt = $pdo->prepare("
    SELECT m.*, u.name, u.email, u.user_id
    FROM managers m
    JOIN users u ON m.user_id = u.user_id
    WHERE m.manager_id = ?
");
$stmt->execute([$manager_id]);
$manager = $stmt->fetch();

if (!$manager) {
    $_SESSION['error'] = "Manager not found.";
    header("Location: managers.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update users table
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, email = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $manager['user_id']
        ]);

        // Update managers table
        $stmt = $pdo->prepare("
            UPDATE managers 
            SET department = ?
            WHERE manager_id = ?
        ");
        $stmt->execute([
            $_POST['department'],
            $manager_id
        ]);

        // Update password if provided
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET password = ?
                WHERE user_id = ?
            ");
            $stmt->execute([
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $manager['user_id']
            ]);
        }

        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Manager updated successfully.";
        header("Location: managers.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error updating manager: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1>Edit Manager</h1>
    <a href="managers.php" class="btn-secondary">Back to Managers</a>
</div>

<div class="content-card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($manager['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($manager['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($manager['department']); ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Update Manager</button>
            <a href="managers.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 