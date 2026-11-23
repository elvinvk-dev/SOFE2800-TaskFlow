<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = currentUserId();
if ($userId === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_POST;
}

$taskId = isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
if ($taskId === false || $taskId === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid task ID is required']);
    exit;
}

try {
    $deleteSql = 'DELETE FROM tasks
                  WHERE id = :id AND user_id = :user_id';
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([
        ':id' => $taskId,
        ':user_id' => $userId,
    ]);

    if ($deleteStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Task deleted',
        'id' => $taskId,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}