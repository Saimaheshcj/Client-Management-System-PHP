<?php
require_once 'includes/header.php';

// Check if client ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No client ID provided.";
    header("Location: clients.php");
    exit();
}

$client_id = $_GET['id'];

// Get client and user information
$stmt = $pdo->prepare("
    SELECT c.*, u.name, u.email, u.user_id
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.client_id = ?
");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    $_SESSION['error'] = "Client not found.";
    header("Location: clients.php");
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
            $client['user_id']
        ]);

        // Update clients table
        $stmt = $pdo->prepare("
            UPDATE clients 
            SET manager_id = ?, company = ?, phone = ?, address = ?
            WHERE client_id = ?
        ");
        $stmt->execute([
            $_POST['manager_id'],
            $_POST['company'],
            $_POST['phone'],
            $_POST['address'],
            $client_id
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
                $client['user_id']
            ]);
        }

        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Client updated successfully.";
        header("Location: clients.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error updating client: " . $e->getMessage();
    }
}

// Get all managers
$stmt = $pdo->prepare("
    SELECT m.manager_id, u.name
    FROM managers m
    JOIN users u ON m.user_id = u.user_id
    ORDER BY u.name
");
$stmt->execute();
$managers = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Edit Client</h1>
    <a href="clients.php" class="btn-secondary">Back to Clients</a>
</div>

<div class="content-card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="company">Company</label>
            <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($client['company']); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" required><?php echo htmlspecialchars($client['address']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="manager_id">Manager</label>
            <select id="manager_id" name="manager_id" required>
                <option value="">Select Manager</option>
                <?php foreach ($managers as $manager): ?>
                <option value="<?php echo $manager['manager_id']; ?>" 
                    <?php echo ($manager['manager_id'] == $client['manager_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($manager['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Update Client</button>
            <a href="clients.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 