<?php
$page_title = 'My Projects';
require_once '../includes/header.php';

// Fetch client's projects with progress
$stmt = $pdo->prepare(
    "
    SELECT p.*,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id) AS total_tasks,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id AND t.status = 'completed') AS completed_tasks
    FROM projects p
    WHERE p.client_id = ?
    ORDER BY p.created_at DESC
    "
);
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>My Projects</h1>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): 
                $progress = $project['total_tasks'] > 0
                    ? round(($project['completed_tasks'] / $project['total_tasks']) * 100)
                    : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td>
                    <span class="status-badge status-<?php echo $project['status']; ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </td>
                <td>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $progress; ?>%;"></div>
                        <span><?php echo $progress; ?>%</span>
                    </div>
                </td>
                <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($project['end_date'])); ?></td>
                <td>
                    <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?> 