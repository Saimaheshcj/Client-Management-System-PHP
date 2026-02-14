<?php
$page_title = 'My Invoices';
require_once '../includes/header.php';

// Fetch client's invoices
$stmt = $pdo->prepare('SELECT i.*, p.title AS project_title FROM invoices i LEFT JOIN projects p ON i.project_id = p.project_id WHERE i.client_id = ? ORDER BY i.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class='page-header'>
    <h1>My Invoices</h1>
</div>

<div class='content-card'>
    <table class='data-table'>
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
            <?php foreach ($invoices as $inv): ?>
            <tr>
                <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                <td><?php echo htmlspecialchars($inv['project_title'] ?? 'General Invoice'); ?></td>
                <td>$<?php echo number_format($inv['amount'], 2); ?></td>
                <td><?php echo ucfirst($inv['status']); ?></td>
                <td><a href='invoice_details.php?id=<?php echo $inv['invoice_id']; ?>' class='btn-small'>View Details</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?> 