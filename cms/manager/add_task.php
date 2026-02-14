<?php
// Add Task for Manager
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'Add Task';
require_once dirname(__DIR__) . '/includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = intval($_POST['project_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = intval($_POST['assigned_to']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];

    // Verify project belongs to manager
    $stmt = $pdo->prepare("SELECT project_id FROM projects WHERE project_id = ? AND manager_id = ?");
    $stmt->execute([$project_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = 'Invalid project selected.';
        header('Location: projects.php');
        exit;
    }

    // Insert new task
    $stmt = $pdo->prepare(
        "INSERT INTO tasks (project_id, title, description, assigned_to, status, priority, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->execute([$project_id, $title, $description, $assigned_to, $status, $priority, $due_date]);

    $_SESSION['success'] = 'Task added successfully.';
    header('Location: project_details.php?id=' . $project_id);
    exit;
}

// Get manager's projects
$stmt = $pdo->prepare("SELECT project_id, title FROM projects WHERE manager_id = ? ORDER BY start_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get potential assignees (team members)
$stmt = $pdo->prepare(
    "SELECT DISTINCT u.user_id, u.name
     FROM project_team pt
     JOIN users u ON pt.user_id = u.user_id
     JOIN projects p ON pt.project_id = p.project_id
     WHERE p.manager_id = ?
     ORDER BY u.name"
);
$stmt->execute([$_SESSION['user_id']]);
$assignees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>Add New Task</h1>
    <a href="projects.php" class="btn-secondary">Back to Projects</a>
</div>

<div class="content-card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="project_id">Project</label>
            <select id="project_id" name="project_id" required>
                <option value="">Select Project</option>
                <?php foreach ($projects as $proj): ?>
                <option value="<?php echo $proj['project_id']; ?>"><?php echo htmlspecialchars($proj['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Task Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="assigned_to">Assign To</label>
            <select id="assigned_to" name="assigned_to" required>
                <option value="">Select Team Member</option>
                <?php foreach ($assignees as $user): ?>
                <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" required>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Task</button>
            <a href="projects.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 