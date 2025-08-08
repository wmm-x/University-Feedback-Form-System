<?php
header('Content-Type: application/json');
require_once __DIR__ . '/dbh.inc.php'; 
$response = [
    'faculties' => [],
    'departments' => [],
];

// add a department
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_department') {
        $department_name = trim($_POST['department_name'] ?? '');
        $faculty_id = intval($_POST['faculty_id'] ?? 0);

        if ($department_name === '' || $faculty_id <= 0) {
            echo json_encode(['error' => 'Department name and valid faculty are required.']);
            exit;
        }
        $chk = $conn->prepare("SELECT id FROM faculty WHERE id = ?");
        if (!$chk) {
            echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        $chk->bind_param('i', $faculty_id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows === 0) {
            echo json_encode(['error' => 'Selected faculty does not exist.']);
            $chk->close();
            exit;
        }
        $chk->close();

        $dup = $conn->prepare("SELECT id FROM department WHERE faculty_id = ? AND name = ?");
        $dup->bind_param('is', $faculty_id, $department_name);
        $dup->execute();
        $dup->store_result();
        if ($dup->num_rows > 0) {
            echo json_encode(['error' => 'That department already exists for the selected faculty.']);
            $dup->close();
            exit;
        }
        $dup->close();

        $stmt = $conn->prepare("INSERT INTO department (faculty_id, name) VALUES (?, ?)");
        if (!$stmt) {
            echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param('is', $faculty_id, $department_name);
        if ($stmt->execute()) {
            echo json_encode(['success' => "Department '{$department_name}' added."]);
            $stmt->close();
            exit;
        } else {
            echo json_encode(['error' => 'Insert failed: ' . $stmt->error]);
            $stmt->close();
            exit;
        }
    }
}

$faculty_id = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

if ($faculty_id > 0) {
   
    $stmt = $conn->prepare("SELECT id, name FROM department WHERE faculty_id = ? ORDER BY name");
    if ($stmt) {
        $stmt->bind_param('i', $faculty_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $departments = [];
        while ($row = $res->fetch_assoc()) {
            $departments[] = ['id' => intval($row['id']), 'name' => $row['name']];
        }
        $stmt->close();
        echo json_encode(['departments' => $departments]);
        exit;
    } else {
        echo json_encode(['error' => $conn->error]);
        exit;
    }
} else {

    $result = $conn->query("SELECT id, name FROM faculty ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response['faculties'][] = ['id' => intval($row['id']), 'name' => $row['name']];
        }
    } else {
        $response['faculties_error'] = $conn->error;
    }
    echo json_encode($response);
    exit;
}
