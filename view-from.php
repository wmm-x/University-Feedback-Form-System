<?php
// ------- PHP: Fetch your form, questions, and options here ---------
include './inc/dbh.inc.php';

$form_key = $_GET['key'] ?? null;
if (!$form_key) {
  echo "<h3>Invalid form key provided.</h3>";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM feedback_forms WHERE form_key = ? AND status = 'active'");
$stmt->execute([$form_key]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    include './src/template/inactive.html';
  exit;
}

$qStmt = $pdo->prepare("SELECT * FROM form_questions WHERE form_id = ?");
$qStmt->execute([$form['id']]);
$questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

$optionMap = [];
foreach ($questions as $q) {
  if (in_array($q['question_type'], ['radio', 'checkbox'])) {
    $oStmt = $pdo->prepare("SELECT * FROM form_options WHERE question_id = ?");
    $oStmt->execute([$q['id']]);
    $optionMap[$q['id']] = $oStmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($form['title']); ?> | University of Vavuniya</title>
  <link rel="icon" href="./src/img/logo.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2c3e50;
      --primary-light: #34495e;
      --text-primary: #2c3e50;
      --text-secondary: #5a6c7d;
      --text-muted: #7f8c8d;
      --border-light: #e8ecef;
      --border-medium: #d1d9e0;
      --bg-primary: #ffffff;
      --bg-secondary: #f8f9fa;
      --bg-input: #ffffff;
      --shadow-light: 0 2px 4px rgba(0, 0, 0, 0.04);
      --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.07);
      --required-color: #dc3545;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--bg-secondary);
      color: var(--text-primary);
      line-height: 1.6;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Header */
    header {
      background: var(--bg-primary);
      border-bottom: 1px solid var(--border-light);
      padding: 1rem 2rem;
      box-shadow: var(--shadow-light);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    header img {
      height: 40px;
      width: 40px;
      border-radius: 4px;
    }

    .header-text {
      flex: 1;
    }

    .header-text h1 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-primary);
    }

    .header-text p {
      margin: 0;
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    /* Main Content */
    main {
      flex: 1;
      padding: 2rem 1rem;
      max-width: 800px;
      margin: 0 auto;
      width: 100%;
    }

    .form-container {
      background: var(--bg-primary);
      border-radius: 8px;
      box-shadow: var(--shadow-medium);
      border: 1px solid var(--border-light);
      overflow: hidden;
    }

    .form-header {
      padding: 2rem 2rem 1rem;
      border-bottom: 1px solid var(--border-light);
    }

    .form-title {
      margin: 0 0 0.5rem;
      font-size: 1.75rem;
      font-weight: 600;
      color: var(--text-primary);
    }

    .form-description {
      margin: 0;
      color: var(--text-secondary);
      font-size: 1rem;
      font-weight: 500;
      white-space: pre-line;
    }

    .form-content {
      padding: 2rem;
    }

    .question-group {
      margin-bottom: 2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid var(--border-light);
    }

    .question-group:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .question-label {
      display: block;
      margin-bottom: 0.75rem;
      font-size: 1rem;
      font-weight: 500;
      color: var(--text-primary);
      line-height: 1.5;
    }

    .question-number {
      color: var(--text-muted);
      font-weight: 400;
      margin-right: 0.5rem;
    }

    .required {
      color: var(--required-color);
      margin-left: 0.25rem;
    }

    /* Form Inputs */
    input[type="text"],
    textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-medium);
      border-radius: 4px;
      font-size: 1rem;
      font-family: inherit;
      background: var(--bg-input);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    input[type="text"]:focus,
    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    /* Radio and Checkbox Options */
    .option-group {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .option-item {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 4px;
      transition: background-color 0.2s ease;
    }

    .option-item:hover {
      background: var(--bg-secondary);
    }

    .option-item input[type="radio"],
    .option-item input[type="checkbox"] {
      margin: 0;
      margin-top: 0.125rem;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .option-item label {
      flex: 1;
      font-size: 1rem;
      color: var(--text-primary);
      cursor: pointer;
      margin: 0;
    }

    /* Submit Button */
    .submit-section {
      padding: 2rem;
      border-top: 1px solid var(--border-light);
      background: var(--bg-secondary);
    }

    .submit-btn {
      width: 100%;
      padding: 1rem 2rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      font-weight: 500;
      font-family: inherit;
      cursor: pointer;
      transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .submit-btn:hover {
      background: var(--primary-light);
    }

    .submit-btn:active {
      transform: translateY(1px);
    }

    /* Footer */
    footer {
      background: var(--bg-primary);
      border-top: 1px solid var(--border-light);
      padding: 1.5rem 2rem;
      text-align: center;
      color: var(--text-muted);
      font-size: 0.875rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      header {
        padding: 1rem;
      }

      .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }

      .header-text h1 {
        font-size: 1.25rem;
      }

      main {
        padding: 1rem 0.5rem;
      }

      .form-header,
      .form-content,
      .submit-section {
        padding: 1.5rem 1rem;
      }

      .form-title {
        font-size: 1.5rem;
      }

      .question-group {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .header-text h1 {
        font-size: 1.125rem;
      }

      .form-title {
        font-size: 1.375rem;
      }

      .form-header,
      .form-content,
      .submit-section {
        padding: 1rem;
      }
    }
  </style>
</head>

<body>
  <header>
    <div class="header-content">
      <img src="./src/img/logo.png" alt="University of Vavuniya Logo">
      <div class="header-text">
        <h1>University of Vavuniya</h1>
        <p>Student Feedback Portal</p>
      </div>
    </div>
  </header>

  <main>
    <div class="form-container">
      <div class="form-header">
  <h2 class="form-title"><?php echo htmlspecialchars($form['title']); ?></h2>

  <?php if (!empty($form['description'])): ?>
    <?php
      $desc = str_replace(["\r\n", "\r"], "\n", $form['description']);
      $desc = htmlspecialchars(trim($desc));
      $desc = preg_replace("/\n{2,}/", "</p><p>", $desc);
      $desc = str_replace("\n", "<br>", $desc);
    ?>
    <div class="form-description"><p><?php echo $desc; ?></p></div>
  <?php endif; ?>
</div>


      <form action="./inc/response.php" method="POST" autocomplete="off">
        <div class="form-content">
          <input type="hidden" name="form_id" value="<?php echo $form['id']; ?>">
          
          <?php foreach ($questions as $index => $q): ?>
            <div class="question-group">
              <label class="question-label">
                <span class="question-number"><?php echo ($index + 1); ?>.</span>
                <?php echo htmlspecialchars($q['question_text']); ?>
                <?php if ($q['is_required']) echo '<span class="required">*</span>'; ?>
              </label>

              <?php if ($q['question_type'] === 'short'): ?>
                <input 
                  type="text" 
                  name="responses[<?php echo $q['id']; ?>]" 
                  placeholder="Enter your answer"
                  <?php echo $q['is_required'] ? 'required' : ''; ?>
                >

              <?php elseif ($q['question_type'] === 'paragraph'): ?>
                <textarea 
                  name="responses[<?php echo $q['id']; ?>]" 
                  placeholder="Type your detailed response"
                  <?php echo $q['is_required'] ? 'required' : ''; ?>
                ></textarea>

              <?php elseif (in_array($q['question_type'], ['radio', 'checkbox'])): ?>
                <div class="option-group">
                  <?php foreach ($optionMap[$q['id']] ?? [] as $opt): ?>
                    <div class="option-item">
                      <input
                        type="<?php echo $q['question_type']; ?>"
                        id="q<?php echo $q['id']; ?>_opt<?php echo $opt['id']; ?>"
                        name="responses[<?php echo $q['id']; ?>]<?php echo $q['question_type'] === 'checkbox' ? '[]' : ''; ?>"
                        value="<?php echo htmlspecialchars($opt['option_text']); ?>"
                        <?php echo $q['is_required'] && $q['question_type'] === 'radio' ? 'required' : ''; ?>
                      >
                      <label for="q<?php echo $q['id']; ?>_opt<?php echo $opt['id']; ?>">
                        <?php echo htmlspecialchars($opt['option_text']); ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="submit-section">
          <button class="submit-btn" type="submit">Submit Response</button>
        </div>
      </form>
    </div>
  </main>

  <footer>
    &copy; <?php echo date('Y'); ?> Department of ICT, Faculty of Technological Studies — University of Vavuniya
  </footer>
</body>

</html>