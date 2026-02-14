<?php
require_once 'includes/header.php';

// Get statistics
$stats = [
    'managers' => $pdo->query("SELECT COUNT(*) FROM managers")->fetchColumn(),
    'clients' => $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'active_projects' => $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'ongoing'")->fetchColumn()
];

// Get recent projects with client and manager names
$stmt = $pdo->prepare(
    "
    SELECT p.*, cu.name AS client_name, mu.name AS manager_name
    FROM projects p
    JOIN clients c ON p.client_id = c.client_id
    JOIN users cu ON c.user_id = cu.user_id
    JOIN managers m ON p.manager_id = m.manager_id
    JOIN users mu ON m.user_id = mu.user_id
    ORDER BY p.start_date DESC
    LIMIT 5
    "
);
$stmt->execute();
$recent_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-header">
    <h1>Dashboard Overview</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Managers</h3>
        <p><?php echo $stats['managers']; ?></p>
        <a href="managers.php" class="btn-link">View Managers</a>
    </div>
    <div class="stat-card">
        <h3>Total Clients</h3>
        <p><?php echo $stats['clients']; ?></p>
        <a href="clients.php" class="btn-link">View Clients</a>
    </div>
    <div class="stat-card">
        <h3>Total Projects</h3>
        <p><?php echo $stats['projects']; ?></p>
        <a href="projects.php" class="btn-link">View Projects</a>
    </div>
    <div class="stat-card">
        <h3>Active Projects</h3>
        <p><?php echo $stats['active_projects']; ?></p>
        <a href="projects.php?status=ongoing" class="btn-link">View Active</a>
    </div>
</div>

<div class="recent-projects">
    <h2>Recent Projects</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Client</th>
                <th>Manager</th>
                <th>Start Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td><?php echo htmlspecialchars($project['client_name']); ?></td>
                <td><?php echo htmlspecialchars($project['manager_name']); ?></td>
                <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                <td>
                    <span class="status-badge status-<?php echo $project['status']; ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </td>
                <td>
                    <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?> 