<?php
// Initialize session and database connection
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/header.php';

// Get manager's projects with client names
$stmt = $pdo->prepare("
    SELECT p.*, u.name AS client_name,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id) as total_tasks,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id AND t.status = 'completed') as completed_tasks
    FROM projects p
    JOIN clients c ON p.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    WHERE p.manager_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>My Projects</h1>
    <a href="create_project.php" class="btn-primary">Create New Project</a>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Client</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td><?php echo htmlspecialchars($project['client_name']); ?></td>
                <td>
                    <span class="status-badge status-<?php echo $project['status']; ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </td>
                <td>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php 
                            $progress = $project['total_tasks'] > 0 
                                ? round(($project['completed_tasks'] / $project['total_tasks']) * 100) 
                                : 0;
                            echo $progress;
                        ?>%"></div>
                        <span><?php echo $progress; ?>%</span>
                    </div>
                </td>
                <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($project['end_date'])); ?></td>
                <td>
                    <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View Details</a>
                    <a href="edit_project.php?id=<?php echo $project['project_id']; ?>" class="btn-small">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 