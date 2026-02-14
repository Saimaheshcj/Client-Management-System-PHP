<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'My Invoices';
require_once dirname(__DIR__) . '/includes/header.php';

// Fetch invoices for manager's clients
$stmt = $pdo->prepare(
    "
    SELECT i.invoice_id, i.invoice_number, p.title AS project_title,
           u.name AS client_name, i.amount, i.status, i.due_date, i.created_at
    FROM invoices i
    JOIN clients c ON i.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN projects p ON i.project_id = p.project_id
    WHERE c.manager_id = ?
    ORDER BY i.created_at DESC
    "
);
$stmt->execute([$_SESSION['user_id']]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>My Invoices</h1>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Client</th>
                <th>Project</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv): ?>
            <tr>
                <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                <td><?php echo htmlspecialchars($inv['client_name']); ?></td>
                <td><?php echo htmlspecialchars($inv['project_title'] ?? 'General Invoice'); ?></td>
                <td>$<?php echo number_format($inv['amount'], 2); ?></td>
                <td><?php echo ucfirst($inv['status']); ?></td>
                <td><?php echo date('M j, Y', strtotime($inv['due_date'])); ?></td>
                <td>
                    <a href="invoice_details.php?id=<?php echo $inv['invoice_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 