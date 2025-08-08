<?php
header('Content-Type: application/json');
include 'dbh.inc.php'; 

$term = trim($_GET['q'] ?? '');
$role = 'lecture';
$params = [];
$types = 's';

$sql = "
  SELECT id, username, email, department
  FROM `user`
  WHERE role = ?
";

$params[] = $role;

if ($term !== '') {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR department LIKE ?)";
    $like = "%{$term}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

$sql .= " ORDER BY username LIMIT 50";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$lecturers = [];
while ($row = $result->fetch_assoc()) {
    $lecturers[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'department' => $row['department'],
    ];
}
$stmt->close();

echo json_encode(['lecturers' => $lecturers]);
