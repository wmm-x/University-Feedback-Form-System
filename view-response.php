<?php
session_start();
if ($_SESSION["role"] != "lecture") {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}
include './inc/dbh.inc.php';

$form_id = $_GET['form_id'] ?? null;
if (!$form_id) {
    echo "<h3>No form ID provided.</h3>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM feedback_forms WHERE id = ? AND status = 'active'");
$stmt->execute([$form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) {
    echo "<h3>Form not found or inactive.</h3>";
    exit;
}

$qStmt = $pdo->prepare("SELECT * FROM form_questions WHERE form_id = ?");
$qStmt->execute([$form_id]);
$questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

$questionIds = array_column($questions, 'id');
$questionMap = [];
foreach ($questions as $q) {
    $questionMap[$q['id']] = $q['question_text'];
}

$resStmt = $pdo->prepare("SELECT * FROM feedback_responses WHERE form_id = ? ORDER BY submitted_at DESC");
$resStmt->execute([$form_id]);
$responses = $resStmt->fetchAll(PDO::FETCH_ASSOC);
$responseCount = count($responses);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Response for <?php echo htmlspecialchars($form['title'] ?? 'Unnamed Form'); ?></title>
    <link rel="icon" href="./src/img/logo.png" type="image/png">
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
            --bg-card: #ffffff;
            --shadow-light: 0 2px 4px rgba(0, 0, 0, 0.04);
            --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.07);
            --radius: 8px;
            --transition: 0.2s ease;
            --empty-color: #999999;
            --required-color: #dc3545;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: var(--primary);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        header img {
            height: 45px;
        }

        header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        main {
            flex: 1;
            width: 100%;
            max-width: 1100px;
            margin: 1.5rem auto;
            padding: 0 1rem;
        }

        .form-header {
            background: var(--bg-card);
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-light);
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            justify-content: space-between;
        }

        .form-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-title {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
            color: var(--text-primary);
        }

        .subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .view-toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-medium);
            background: white;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .stats {
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-light);
        }

        .response-block {
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
            box-shadow: var(--shadow-light);
        }

        .response-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .question {
            margin-bottom: 10px;
            padding: 8px;
            background: var(--bg-secondary);
            border-radius: 6px;
        }

        .question strong {
            display: block;
            margin-bottom: 4px;
        }

        .empty {
            color: var(--empty-color);
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
            margin-bottom: 1rem;
        }

        thead {
            background: var(--primary-light);
            color: white;
        }

        th,
        td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
        }

        th {
            font-weight: 600;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .no-responses {
            text-align: center;
            padding: 2rem;
            background: white;
            border: 1px dashed var(--border-medium);
            border-radius: var(--radius);
            margin-top: 1rem;
        }

        footer {
            background: white;
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-light);
        }

        @media (max-width: 900px) {
            .form-header {
                flex-direction: column;
                align-items: flex-start;
            }

            table,
            th,
            td {
                font-size: 12px;
            }

            header h1 {
                font-size: 1rem;
            }

            .form-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>

<body>

    <header>
        <img src="./src/img/logo.png" alt="UoV Logo">
        <h1>University of Vavuniya - Student Feedback Portal</h1>
    </header>

    <main>
        <div class="form-header">
            <div class="form-info">
                <h2 class="form-title">Response for "<?php echo htmlspecialchars($form['title'] ?? 'Unnamed Form'); ?>"</h2>
                <div class="subtitle">Viewing feedback submitted by students.</div>
                <div class="stats"><?php echo $responseCount; ?> response<?php echo $responseCount === 1 ? '' : 's'; ?> submitted.</div>
            </div>
            <div class="view-toggle-wrapper">
                <label for="viewToggle">Display:</label>
                <select id="viewToggle" onchange="toggleView()">
                    <option value="table" selected>Table</option>
                    <option value="list">List</option>
                </select>
            </div>
        </div>

        <?php if ($responseCount === 0): ?>
            <div class="no-responses">
                <p><strong>No responses yet.</strong> Once students submit feedback, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div id="tableView">
                <div class="card">
                    <table>
                        <thead>
                            <tr>
                                <th>Submitted At</th>
                                <?php foreach ($questionIds as $qid): ?>
                                    <th><?php echo htmlspecialchars($questionMap[$qid]); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responses as $response): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($response['submitted_at']); ?></td>
                                    <?php
                                    $aStmt = $pdo->prepare("SELECT * FROM response_answers WHERE response_id = ?");
                                    $aStmt->execute([$response['id']]);
                                    $answers = $aStmt->fetchAll(PDO::FETCH_ASSOC);

                                    $answerMap = [];
                                    foreach ($answers as $a) {
                                        $qid = $a['question_id'];
                                        $answerMap[$qid][] = $a['answer_text'];
                                    }

                                    foreach ($questionIds as $qid):
                                        $ans = $answerMap[$qid] ?? [];
                                    ?>
                                        <td>
                                            <?php
                                            if (count($ans)) {
                                                echo htmlspecialchars(implode(', ', $ans));
                                            } else {
                                                echo '<span class="empty">No answer</span>';
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="listView" style="display: none;">
                <?php foreach ($responses as $response): ?>
                    <div class="response-block">
                        <div class="response-meta">
                            <div><strong>Submitted At:</strong> <?php echo htmlspecialchars($response['submitted_at']); ?></div>
                        </div>
                        <?php
                        $aStmt = $pdo->prepare("SELECT * FROM response_answers WHERE response_id = ?");
                        $aStmt->execute([$response['id']]);
                        $answers = $aStmt->fetchAll(PDO::FETCH_ASSOC);

                        $answerMap = [];
                        foreach ($answers as $a) {
                            $answerMap[$a['question_id']][] = $a['answer_text'];
                        }
                        ?>
                        <?php foreach ($questionMap as $qid => $text): ?>
                            <?php $ans = $answerMap[$qid] ?? []; ?>
                            <div class="question">
                                <strong><?php echo htmlspecialchars($text); ?></strong>
                                <div>
                                    <?php
                                    if (count($ans)) {
                                        echo htmlspecialchars(implode(', ', $ans));
                                    } else {
                                        echo '<span class="empty">No answer</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> Department of ICT, Faculty of Technological Studies — University of Vavuniya
    </footer>

    <script>
        function toggleView() {
            const view = document.getElementById('viewToggle').value;
            document.getElementById('tableView').style.display = (view === 'table') ? 'block' : 'none';
            document.getElementById('listView').style.display = (view === 'list') ? 'block' : 'none';
        }
    </script>

</body>

</html>
