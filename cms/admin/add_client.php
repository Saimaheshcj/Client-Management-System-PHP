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
            VALUES (?, ?, ?, 'client')
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT)
        ]);
        $user_id = $pdo->lastInsertId();

        // Insert into clients table
        $stmt = $pdo->prepare("
            INSERT INTO clients (user_id, manager_id, company, phone, address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $_POST['manager_id'],
            $_POST['company'],
            $_POST['phone'],
            $_POST['address']
        ]);

        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Client added successfully.";
        header("Location: clients.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error adding client: " . $e->getMessage();
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
    <h1>Add New Client</h1>
    <a href="clients.php" class="btn-secondary">Back to Clients</a>
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
            <label for="company">Company</label>
            <input type="text" id="company" name="company" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" required></textarea>
        </div>

        <div class="form-group">
            <label for="manager_id">Manager</label>
            <select id="manager_id" name="manager_id" required>
                <option value="">Select Manager</option>
                <?php foreach ($managers as $manager): ?>
                <option value="<?php echo $manager['manager_id']; ?>">
                    <?php echo htmlspecialchars($manager['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Client</button>
            <a href="clients.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 