<?php
// Initialize session and database connection
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/header.php';

// Get manager's clients
$stmt = $pdo->prepare(
    "
    SELECT c.client_id, u.name, u.email, c.company, c.phone, c.address
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.manager_id = ?
    ORDER BY u.name
    "
);
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>My Clients</h1>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Address</th>
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
                <td><?php echo htmlspecialchars($client['address']); ?></td>
                <td>
                    <a href="client_details.php?id=<?php echo $client['client_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 