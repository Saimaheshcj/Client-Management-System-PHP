<?php
// Edit Project for Manager
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'Edit Project';
require_once dirname(__DIR__) . '/includes/header.php';

// Validate project ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No project ID provided.';
    header('Location: projects.php');
    exit;
}
$project_id = intval($_GET['id']);

// Fetch project and check ownership
$stmt = $pdo->prepare(
    "SELECT * FROM projects WHERE project_id = ? AND manager_id = ?"
);
$stmt->execute([$project_id, $_SESSION['user_id']]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: projects.php');
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare(
            "UPDATE projects SET title = ?, description = ?, client_id = ?, status = ?, start_date = ?, end_date = ? WHERE project_id = ?"
        );
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['client_id'],
            $_POST['status'],
            $_POST['start_date'],
            $_POST['end_date'],
            $project_id
        ]);
        $_SESSION['success'] = 'Project updated successfully.';
        header('Location: projects.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating project: ' . $e->getMessage();
    }
}

// Get clients for dropdown
$stmt = $pdo->prepare(
    "SELECT c.client_id, u.name FROM clients c JOIN users u ON c.user_id = u.user_id WHERE c.manager_id = ? ORDER BY u.name"
);
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>Edit Project</h1>
    <a href="projects.php" class="btn-secondary">Back to Projects</a>
</div>

<div class="content-card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="client_id">Client</label>
            <select id="client_id" name="client_id" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $client): ?>
                <option value="<?php echo $client['client_id']; ?>" <?php if ($client['client_id'] == $project['client_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($client['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($project['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="pending" <?php if ($project['status']=='pending') echo 'selected'; ?>>Pending</option>
                <option value="in_progress" <?php if ($project['status']=='in_progress') echo 'selected'; ?>>In Progress</option>
                <option value="completed" <?php if ($project['status']=='completed') echo 'selected'; ?>>Completed</option>
                <option value="on_hold" <?php if ($project['status']=='on_hold') echo 'selected'; ?>>On Hold</option>
            </select>
        </div>
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $project['start_date']; ?>" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $project['end_date']; ?>">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Update Project</button>
            <a href="projects.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 