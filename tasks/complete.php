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
    $selectSql = 'SELECT id, user_id, title, category, deadline, status, created_at
                  FROM tasks
                  WHERE id = :id AND user_id = :user_id
                  LIMIT 1';
    $selectStmt = $pdo->prepare($selectSql);
    $selectStmt->execute([
        ':id' => $taskId,
        ':user_id' => $userId,
    ]);
    $task = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit;
    }

    // Allows passing an explicit `status` ('pending' or 'completed'), or toggles if omitted.
    if (isset($data['status']) && $data['status'] !== '') {
        $nextStatus = trim((string) $data['status']);
        if (!in_array($nextStatus, ['pending', 'completed'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Status must be pending or completed']);
            exit;
        }
    } else {
        $nextStatus = ($task['status'] === 'completed') ? 'pending' : 'completed';
    }

    $updateSql = 'UPDATE tasks
                  SET status = :status
                  WHERE id = :id AND user_id = :user_id';
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':status' => $nextStatus,
        ':id' => $taskId,
        ':user_id' => $userId,
    ]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Task status updated',
        'task' => [
            'id' => (int) $task['id'],
            'user_id' => (int) $task['user_id'],
            'title' => $task['title'],
            'category' => $task['category'],
            'deadline' => $task['deadline'],
            'status' => $nextStatus,
            'created_at' => $task['created_at'],
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}