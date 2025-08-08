<?php
include 'dbh.inc.php';

$data = json_decode(file_get_contents("php://input"), true);
if (
  !$data || 
  !isset($data['title'], $data['type'], $data['questions'], $data['lecturer_id'])
) {
  echo json_encode(['success' => false, 'message' => 'Invalid form data']);
  exit;
}

try {
  $pdo->beginTransaction();

  //Generate  16-char form key
  $formKey = bin2hex(random_bytes(8));


  $stmt = $pdo->prepare("
    INSERT INTO feedback_forms (title, description, type, form_key, status, Lecture_ID) 
    VALUES (?, ?, ?, ?, 'active', ?)
  ");
  $stmt->execute([
    $data['title'],
    $data['description'],
    $data['type'],
    $formKey,
    $data['lecturer_id']
  ]);
  $formId = $pdo->lastInsertId();

  // Insert questions
  $qStmt = $pdo->prepare("INSERT INTO form_questions (form_id, question_text, question_type, is_required) VALUES (?, ?, ?, ?)");
  $oStmt = $pdo->prepare("INSERT INTO form_options (question_id, option_text) VALUES (?, ?)");

  foreach ($data['questions'] as $question) {
    $qStmt->execute([$formId, $question['question_text'], $question['question_type'], $question['is_required']]);
    $questionId = $pdo->lastInsertId();

    if (in_array($question['question_type'], ['radio', 'checkbox'])) {
      foreach ($question['options'] as $opt) {
        $oStmt->execute([$questionId, $opt]);
      }
    }
  }

  $pdo->commit();
  $host     = $_SERVER['HTTP_HOST'];
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $formLink = $protocol . '://' . $host . '/project2/view-from.php?key=' . $formKey;


  echo json_encode([
    'success' => true,
    'form_key' => $formKey,
    'form_link' => $formLink,
    'message' => "You can access using this link"
  ]);
} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
