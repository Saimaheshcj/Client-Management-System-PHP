<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt->execute([$_GET['id']]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        header('Content-Type: application/json');
        echo json_encode($project);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Project not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID not provided']);
}
?> 