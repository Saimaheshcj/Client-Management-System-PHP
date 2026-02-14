<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM managers WHERE manager_id = ?");
    $stmt->execute([$_GET['id']]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($manager) {
        header('Content-Type: application/json');
        echo json_encode($manager);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Manager not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Manager ID not provided']);
}
?> 