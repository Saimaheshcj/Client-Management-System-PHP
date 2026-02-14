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

// Get manager's clients
$stmt = $pdo->prepare("
    SELECT c.*, u.name, u.email
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.manager_id = ?
    ORDER BY u.name
");
$stmt->execute([$manager_id]);
$clients = $stmt->fetchAll();

// Get manager's projects
$stmt = $pdo->prepare("
    SELECT p.*, c.company as client_company
    FROM projects p
    JOIN clients c ON p.client_id = c.client_id
    WHERE p.manager_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$manager_id]);
$projects = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Manager Details</h1>
    <div class="header-actions">
        <a href="edit_manager.php?id=<?php echo $manager_id; ?>" class="btn-primary">Edit Manager</a>
        <a href="managers.php" class="btn-secondary">Back to Managers</a>
    </div>
</div>

<div class="content-card">
    <h2>Manager Information</h2>
    <div class="details-grid">
        <div class="detail-item">
            <label>Name:</label>
            <span><?php echo htmlspecialchars($manager['name']); ?></span>
        </div>
        <div class="detail-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($manager['email']); ?></span>
        </div>
        <div class="detail-item">
            <label>Department:</label>
            <span><?php echo htmlspecialchars($manager['department']); ?></span>
        </div>
        <div class="detail-item">
            <label>Created At:</label>
            <span><?php echo date('F j, Y', strtotime($manager['created_at'])); ?></span>
        </div>
    </div>
</div>

<div class="content-card">
    <h2>Assigned Clients</h2>
    <?php if (count($clients) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
            <tr>
                <td><?php echo htmlspecialchars($client['name']); ?></td>
                <td><?php echo htmlspecialchars($client['email']); ?></td>
                <td><?php echo htmlspecialchars($client['company']); ?></td>
                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                <td>
                    <a href="client_details.php?id=<?php echo $client['client_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No clients assigned to this manager.</p>
    <?php endif; ?>
</div>

<div class="content-card">
    <h2>Managed Projects</h2>
    <?php if (count($projects) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Client</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td><?php echo htmlspecialchars($project['client_company']); ?></td>
                <td><?php echo ucfirst($project['status']); ?></td>
                <td><?php echo date('M j, Y', strtotime($project['start_date'])); ?></td>
                <td><?php echo $project['end_date'] ? date('M j, Y', strtotime($project['end_date'])) : 'Not set'; ?></td>
                <td>
                    <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No projects managed by this manager.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 