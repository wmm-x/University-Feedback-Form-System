<?php
header('Content-Type: application/json');
session_start();
include 'dbh.inc.php';
include '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sys_admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$name = trim($_POST['lecturer_name'] ?? '');
$email = trim($_POST['lecturer_email'] ?? '');
$faculty = trim($_POST['faculty_name'] ?? '');
$department = trim($_POST['department_name'] ?? '');
$password = trim($_POST['password'] ?? '');

// validation
if ($name === '' || $email === '' || $faculty === '' || $department === '' || $password === '') {
    echo json_encode(['error' => 'All fields are required.']);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email address.']);
    exit();
}

// Duplicate email check
$dup = $conn->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
$dup->bind_param('s', $email);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) {
    $dup->close();
    echo json_encode(['error' => 'Email already exists.']);
    exit();
}
$dup->close();

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO user (username, email, department, faculty, password, role) VALUES (?, ?, ?, ?, ?, ?)");
$role = 'lecture';
$stmt->bind_param('ssssss', $name, $email, $department, $faculty, $hashed, $role);

if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    $stmt->close();
    $msg='✅ Lecturer account created successfully. crendentials sent via email.';

    $host     = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $loginURL = $protocol . '://' . $host . '/project2/login.php';
    $template = file_get_contents('../src/template/email.html');

    
    $template = str_replace('[STAFF_NAME]', htmlspecialchars($name), $template);
    $template = str_replace('[USERNAME]', htmlspecialchars($email), $template);
    $template = str_replace('[PASSWORD]', htmlspecialchars($password), $template);
    $template = str_replace('[LOGIN_URL]', $loginURL, $template);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';  
        $mail->SMTPAuth   = true;
        $mail->Username   = 'SMTP_USER_NAME';        
        $mail->Password   = 'YOUR_SMTP_KEY';   // SMTP password - keep this secure
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        

        $mail->setFrom('SENDER EMAIL', 'Student Feedback Portal - University of Vavuniya');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->addEmbeddedImage('../src/img/logo.png', 'logo_cid');
        $mail->Subject = 'Your Lecturer Account for Student Feedback Portal';
        $mail->Body    = $template;
        $mail->AltBody = "Dear $name,\n\nYour account has been created.\nUsername: $email\nPassword: $password\nLogin URL: $loginURL\n\nPlease change your password after first login.";

        $mail->send();
    } catch (Exception $e) {
    
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        $msg='✅ Lecturer added successfully. However, email notification failed.';
    }

    echo json_encode([
        'success' => $msg,
        'lecturer' => [
            'id' => intval($new_id),
            'username' => $name,
            'email' => $email,
            'department' => $department,
            'faculty' => $faculty,
            'role' => $role
        ]
    ]);
    exit();

} else {
    $err = $stmt->error;
    $stmt->close();
    echo json_encode(['error' => 'Insert failed: ' . $err]);
    exit();
}
