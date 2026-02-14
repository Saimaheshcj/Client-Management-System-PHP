<?php
require_once 'includes/header.php';

// Get all clients with their manager and user information
$stmt = $pdo->prepare("
    SELECT c.*, u.name, u.email, m.manager_id, m.user_id as manager_user_id
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    JOIN managers m ON c.manager_id = m.manager_id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$clients = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Clients</h1>
    <a href="add_client.php" class="btn-primary">Add New Client</a>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Manager</th>
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
                    <?php
                    $stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
                    $stmt->execute([$client['manager_user_id']]);
                    $manager = $stmt->fetch();
                    echo htmlspecialchars($manager['name']);
                    ?>
                </td>
                <td>
                    <a href="edit_client.php?id=<?php echo $client['client_id']; ?>" class="btn-small">Edit</a>
                    <a href="client_details.php?id=<?php echo $client['client_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>