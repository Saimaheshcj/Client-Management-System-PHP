<?php
// Create Project for Manager
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'Create Project';
require_once dirname(__DIR__) . '/includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insert new project
        $stmt = $pdo->prepare(
            "INSERT INTO projects (title, description, client_id, manager_id, status, start_date, end_date, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['client_id'],
            $_SESSION['user_id'],
            $_POST['status'],
            $_POST['start_date'],
            $_POST['end_date']
        ]);
        $_SESSION['success'] = 'Project created successfully.';
        header('Location: projects.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error creating project: ' . $e->getMessage();
    }
}

// Get clients for dropdown
$stmt = $pdo->prepare(
    "SELECT c.client_id, u.name FROM clients c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.manager_id = ? ORDER BY u.name"
);
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>Create New Project</h1>
    <a href="projects.php" class="btn-secondary">Back to Projects</a>
</div>

<div class="content-card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="client_id">Client</label>
            <select id="client_id" name="client_id" required>
                <option value="">Select Client</option>
<?php foreach ($clients as $client): ?>
                <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
<?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="on_hold">On Hold</option>
            </select>
        </div>
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Create Project</button>
            <a href="projects.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 