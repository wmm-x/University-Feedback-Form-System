<?php
session_start();
include 'dbh.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'lecture') {
    http_response_code(401);
    echo json_encode(['type' => 'unknown', 'status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}


$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['formID'], $input['action'], $input['userID'])) {
    http_response_code(400);
    echo json_encode(['type' => 'unknown', 'status' => 'failed', 'message' => 'Invalid request']);
    exit();
}

$formID = intval($input['formID']);
$action = $input['action'];
$userID = intval($input['userID']);
$type = ($action === 'delete' || $action === 'reset' || $action === 'status') ? $action : 'unknown';

$check = $conn->prepare("SELECT id FROM feedback_forms WHERE id=? AND Lecture_ID=?");
$check->bind_param('ii', $formID, $userID);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['type' => $type, 'status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}
$check->close();

if (isset($input['action'], $input['status']) && $input['action'] === 'status') {
    $newStatus = ($input['status'] === 'active') ? 'active' : 'inactive';
    $stmt = $conn->prepare("UPDATE feedback_forms SET status = ? WHERE id = ? AND Lecture_ID = ?");
    $stmt->bind_param('sii', $newStatus, $formID, $userID);
    if ($stmt->execute()) {
        echo json_encode([
            'type' => 'status',
            'status' => 'success',
            'message' => 'Form status updated',
            'newStatus' => $newStatus
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'type' => 'status',
            'status' => 'failed',
            'message' => 'Could not update form status'
        ]);
    }
    $stmt->close();
    exit();
}
//reset or delete form
try {
    if ($action === 'reset' || $action === 'delete') {
        $responseIds = [];
        $result = $conn->query("SELECT id FROM feedback_responses WHERE form_id = $formID");
        while ($row = $result->fetch_assoc()) {
            $responseIds[] = $row['id'];
        }
        if (!empty($responseIds)) {
            $idList = implode(',', array_map('intval', $responseIds));
           
            $conn->query("DELETE FROM response_answers WHERE response_id IN ($idList)");
            $conn->query("DELETE FROM feedback_responses WHERE form_id = $formID");
        }
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM feedback_forms WHERE id = ? AND Lecture_ID = ?");
        $stmt->bind_param('ii', $formID, $userID);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['type' => 'delete', 'status' => 'success', 'message' => 'Form deleted successfully']);
        exit();
    }

    if ($action === 'reset') {
        echo json_encode(['type' => 'reset', 'status' => 'success', 'message' => 'Form responses reset successfully']);
        exit();
    }

    http_response_code(400);
    echo json_encode(['type' => $type, 'status' => 'failed', 'message' => 'Unknown action.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['type' => $type, 'status' => 'failed', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
