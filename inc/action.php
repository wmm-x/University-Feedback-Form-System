<?php

header('Content-Type: application/json');
require 'dbh.inc.php'; 
require 'function.inc.php';


function respond($arr) {
    echo json_encode($arr);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$email = $_POST['email'] ?? null;

if (!$action) {
    respond(['error' => 'Missing action.']);
}

if ($user_id) {
    $stmt = $conn->prepare("SELECT ID, email, username FROM `user` WHERE ID = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
} elseif ($email) {
    $stmt = $conn->prepare("SELECT ID, email, username FROM `user` WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
} else {
    respond(['error' => 'Missing user identifier (user_id or email).']);
}

if (!$stmt->execute()) {
    respond(['error' => 'Failed to query user.']);
}
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    respond(['error' => 'User not found.']);
}
$user = $res->fetch_assoc();
$stmt->close();
$target_id = (int)$user['ID'];

if ($action === 'reset_password') {
 
    $new_plain = generateRandomPassword(16);
    $hashed = password_hash($new_plain, PASSWORD_BCRYPT);

    $update = $conn->prepare("UPDATE `user` SET password = ? WHERE ID = ?");
    $update->bind_param('si', $hashed, $target_id);
    if ($update->execute()) {
        respond([
            'success' => 'Password reset successfully.',
            'new_password' => $new_plain
        ]);
    } else {
        respond(['error' => 'Failed to update password.']);
    }
    $update->close();

} elseif ($action === 'delete_user') {

    $conn->begin_transaction();

    try {
        // Delete feedback responses
        $stmt1 = $conn->prepare("DELETE FROM `feedback_responses` WHERE Lecture_ID = ?");
        $stmt1->bind_param('i', $target_id);
        if (!$stmt1->execute()) {
            throw new Exception('Failed to delete feedback responses.');
        }
        $stmt1->close();

        //Delete feedback forms
        $stmt2 = $conn->prepare("DELETE FROM `feedback_forms` WHERE Lecture_ID = ?");
        $stmt2->bind_param('i', $target_id);
        if (!$stmt2->execute()) {
            throw new Exception('Failed to delete feedback forms.');
        }
        $stmt2->close();

        //delete the user
        $stmt3 = $conn->prepare("DELETE FROM `user` WHERE ID = ?");
        $stmt3->bind_param('i', $target_id);
        if (!$stmt3->execute()) {
            throw new Exception('Failed to delete user.');
        }
        $stmt3->close();

        $conn->commit();
        respond(['success' => 'User and all associated forms/responses deleted.']);

    } catch (Exception $e) {
        $conn->rollback();
        respond(['error' => $e->getMessage()]);
    }

} else {
    respond(['error' => 'Unknown action.']);
}
