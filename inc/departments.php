<?php
header('Content-Type: application/json');
require_once 'dbh.inc.php'; 

$faculty_id = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

if ($faculty_id > 0) {
    // return departments for one faculty
    $stmt = $conn->prepare("SELECT id, name FROM department WHERE faculty_id = ? ORDER BY name");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    $stmt->bind_param('i', $faculty_id);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    $res = $stmt->get_result();
    $departments = [];
    while ($row = $res->fetch_assoc()) {
        $departments[] = ['id' => intval($row['id']), 'name' => $row['name']];
    }
    $stmt->close();
    echo json_encode(['departments' => $departments]);
    exit;
} else {
    // return all faculties
    $result = $conn->query("SELECT id, name FROM faculty ORDER BY name");
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    $faculties = [];
    while ($row = $result->fetch_assoc()) {
        $faculties[] = ['id' => intval($row['id']), 'name' => $row['name']];
    }
    echo json_encode(['faculties' => $faculties]);
    exit;
}