<?php
session_start();
header('Content-Type: application/json');

include 'dbh.inc.php';


if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: User not logged in']);
    exit;
}

$userid = $_SESSION['userid'];


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Both current and new passwords are required']);
    exit;
}

// Fetch current hashed password from DB
$stmt = $conn->prepare("SELECT password FROM user WHERE ID = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($hashed_password);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Verify current password
if (!password_verify($current_password, $hashed_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Current password is incorrect']);
    $conn->close();
    exit;
}

// Hash new password securely
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the password in the database
$update_stmt = $conn->prepare("UPDATE user SET password = ? WHERE ID = ?");
$update_stmt->bind_param("si", $new_hashed_password, $userid);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update password']);
}

$update_stmt->close();
$conn->close();
?>
