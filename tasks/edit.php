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
    $existingTask = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingTask) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit;
    }

    if (array_key_exists('title', $data)) {
        $title = trim((string) $data['title']);
        if ($title === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title cannot be empty']);
            exit;
        }
        if (mb_strlen($title) > 255) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title must be 255 characters or fewer']);
            exit;
        }
    } else {
        $title = $existingTask['title'];
    }

    if (array_key_exists('category', $data)) {
        $rawCategory = $data['category'];
        $category = ($rawCategory === null || trim((string) $rawCategory) === '')
            ? null
            : trim((string) $rawCategory);
        if ($category !== null && mb_strlen($category) > 50) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category must be 50 characters or fewer']);
            exit;
        }
    } else {
        $category = $existingTask['category'];
    }

    if (array_key_exists('deadline', $data)) {
        $rawDeadline = $data['deadline'];
        $deadline = ($rawDeadline === null || trim((string) $rawDeadline) === '')
            ? null
            : trim((string) $rawDeadline);
        if ($deadline !== null) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $deadline);
            $validDate = $dateObj && $dateObj->format('Y-m-d') === $deadline;
            if (!$validDate) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Deadline must be a valid YYYY-MM-DD date']);
                exit;
            }
        }
    } else {
        $deadline = $existingTask['deadline'];
    }

    $updateSql = 'UPDATE tasks
                  SET title = :title, category = :category, deadline = :deadline
                  WHERE id = :id AND user_id = :user_id';
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':title' => $title,
        ':category' => $category,
        ':deadline' => $deadline,
        ':id' => $taskId,
        ':user_id' => $userId,
    ]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Task updated',
        'task' => [
            'id' => (int) $existingTask['id'],
            'user_id' => (int) $existingTask['user_id'],
            'title' => $title,
            'category' => $category,
            'deadline' => $deadline,
            'status' => $existingTask['status'],
            'created_at' => $existingTask['created_at'],
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}