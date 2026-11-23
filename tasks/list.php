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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$statusFilter = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
if ($statusFilter !== '' && !in_array($statusFilter, ['pending', 'completed'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status filter']);
    exit;
}

$categoryFilter = isset($_GET['category']) ? trim((string) $_GET['category']) : '';

try {
    $sql = 'SELECT id, user_id, title, category, deadline, status, created_at
            FROM tasks
            WHERE user_id = :user_id';
    $params = [':user_id' => $userId];

    if ($statusFilter !== '') {
        $sql .= ' AND status = :status';
        $params[':status'] = $statusFilter;
    }

    if ($categoryFilter !== '') {
        $sql .= ' AND category = :category';
        $params[':category'] = $categoryFilter;
    }

    $sql .= ' ORDER BY created_at DESC, id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tasks = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'user_id' => (int) $row['user_id'],
            'title' => $row['title'],
            'category' => $row['category'],
            'deadline' => $row['deadline'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
        ];
    }, $rows);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'tasks' => $tasks,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}