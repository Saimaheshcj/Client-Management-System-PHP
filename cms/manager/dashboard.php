<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/header.php';

// Get manager's clients
$stmt = $pdo->prepare("
    SELECT c.client_id, u.name AS client_name, u.email
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.manager_id = ?
    ORDER BY u.name
");
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll();

// Get manager's projects
$stmt = $pdo->prepare("
    SELECT p.*, cu.name AS client_name
    FROM projects p
    JOIN clients c ON p.client_id = c.client_id
    JOIN users cu ON c.user_id = cu.user_id
    WHERE p.manager_id = ?
    ORDER BY p.start_date DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Get recent documents
$stmt = $pdo->prepare("
    SELECT d.*, u.name AS client_name
    FROM documents d
    JOIN clients c ON d.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    WHERE c.manager_id = ?
    ORDER BY d.uploaded_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$documents = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Clients</h3>
        <p><?php echo count($clients); ?></p>
        <a href="clients.php" class="btn-link">View All Clients</a>
    </div>
    <div class="stat-card">
        <h3>Active Projects</h3>
        <p><?php echo count($projects); ?></p>
        <a href="projects.php" class="btn-link">View All Projects</a>
    </div>
    <div class="stat-card">
        <h3>Recent Documents</h3>
        <p><?php echo count($documents); ?></p>
        <a href="documents.php" class="btn-link">View All Documents</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h2>Recent Projects</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Start Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                    <td><?php echo htmlspecialchars($project['client_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $project['status']; ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="dashboard-card">
        <h2>Recent Documents</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Document Name</th>
                    <th>Client</th>
                    <th>Upload Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document): ?>
                <tr>
                    <td><?php echo htmlspecialchars($document['title']); ?></td>
                    <td><?php echo htmlspecialchars($document['client_name']); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($document['uploaded_at'])); ?></td>
                    <td>
                        <a href="<?php echo str_replace('../../', '', $document['file_path']); ?>" class="btn-small" target="_blank">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 