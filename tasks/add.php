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

$title = isset($data['title']) ? trim((string) $data['title']) : '';
if ($title === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (mb_strlen($title) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title must be 255 characters or fewer']);
    exit;
}

$rawCategory = $data['category'] ?? null;
$category = ($rawCategory === null || trim((string) $rawCategory) === '')
    ? null
    : trim((string) $rawCategory);

if ($category !== null && mb_strlen($category) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category must be 50 characters or fewer']);
    exit;
}

$rawDeadline = $data['deadline'] ?? null;
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

try {
    $insertSql = 'INSERT INTO tasks (user_id, title, category, deadline, status)
                  VALUES (:user_id, :title, :category, :deadline, :status)';
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':category' => $category,
        ':deadline' => $deadline,
        ':status' => 'pending',
    ]);

    $newTaskId = (int) $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Task created',
        'task' => [
            'id' => $newTaskId,
            'user_id' => $userId,
            'title' => $title,
            'category' => $category,
            'deadline' => $deadline,
            'status' => 'pending',
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}