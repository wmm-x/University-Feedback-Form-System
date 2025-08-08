<?php
include 'dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Invalid request.";
  exit;
}

$form_id = $_POST['form_id'] ?? null;
$responses = $_POST['responses'] ?? [];

if (!$form_id || !is_array($responses)) {
  echo "Invalid form submission.";
  exit;
}

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("SELECT Lecture_ID FROM feedback_forms WHERE id = ?");
  $stmt->execute([$form_id]);
  $lecture = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$lecture) {
    throw new Exception("Invalid form ID.");
  }
  $lecture_id = $lecture['Lecture_ID'];

  // 2. Save to feedback_responses table`
  $stmt = $pdo->prepare("INSERT INTO feedback_responses (form_id, Lecture_ID) VALUES (?, ?)");
  $stmt->execute([$form_id, $lecture_id]);
  $response_id = $pdo->lastInsertId();

  $answerStmt = $pdo->prepare("INSERT INTO response_answers (response_id, question_id, answer_text) VALUES (?, ?, ?)");

  foreach ($responses as $question_id => $answer) {
 
    if (is_array($answer)) {
      foreach ($answer as $option) {
        $answerStmt->execute([$response_id, $question_id, $option]);
      }
    } else {
      $answerStmt->execute([$response_id, $question_id, $answer]);
    }
  }

  $pdo->commit();
  include '../src/template/success.html';

} catch (Exception $e) {
  $pdo->rollBack();
  echo "<h3>Error submitting feedback: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>
