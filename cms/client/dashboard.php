<?php
require_once '../includes/header.php';

// Get client's projects
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id) as total_tasks,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id AND t.status = 'completed') as completed_tasks
    FROM projects p
    WHERE p.client_id = ?
    ORDER BY p.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Get client's documents
$stmt = $pdo->prepare("
    SELECT d.*, u.name as uploaded_by
    FROM documents d
    JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.client_id = ?
    ORDER BY d.uploaded_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$documents = $stmt->fetchAll();

// Get client's invoices
$stmt = $pdo->prepare("
    SELECT i.*, p.title as project_title
    FROM invoices i
    LEFT JOIN projects p ON i.project_id = p.project_id
    WHERE i.client_id = ?
    ORDER BY i.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$invoices = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
</div>

<div class="stats-grid">
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
    <div class="stat-card">
        <h3>Recent Invoices</h3>
        <p><?php echo count($invoices); ?></p>
        <a href="invoices.php" class="btn-link">View All Invoices</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h2>Recent Projects</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Start Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                    <td><?php echo htmlspecialchars($project['status']); ?></td>
                    <td>
                        <?php 
                        $progress = $project['total_tasks'] > 0 
                            ? round(($project['completed_tasks'] / $project['total_tasks']) * 100) 
                            : 0;
                        echo $progress . '%';
                        ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                    <td>
                        <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View Details</a>
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
                    <th>Title</th>
                    <th>Uploaded By</th>
                    <th>Upload Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document): ?>
                <tr>
                    <td><?php echo htmlspecialchars($document['title']); ?></td>
                    <td><?php echo htmlspecialchars($document['uploaded_by']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($document['uploaded_at'])); ?></td>
                    <td>
                        <a href="../uploads/<?php echo $document['file_path']; ?>" class="btn-small" target="_blank">Download</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="dashboard-card">
        <h2>Recent Invoices</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Project</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['project_title']); ?></td>
                    <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($invoice['status']); ?></td>
                    <td>
                        <a href="invoice_details.php?id=<?php echo $invoice['invoice_id']; ?>" class="btn-small">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 