<?php
require_once __DIR__ . '/auth.php';

require_login();

$completion_message = '';
$completion_error = '';
$completion_data = [];
$completion_file = __DIR__ . '/data/lesson-completions.json';

function load_completion_data(string $path): array {
  if (!file_exists($path)) {
    return [];
  }

  $raw = file_get_contents($path);
  $data = json_decode($raw, true);

  return is_array($data) ? $data : [];
}

function save_completion_data(string $path, array $data): void {
  $payload = json_encode($data, JSON_PRETTY_PRINT);
  file_put_contents($path, $payload, LOCK_EX);
}

$completion_data = load_completion_data($completion_file);
$user = current_user();
$username = $user['username'] ?? 'unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
    $completion_error = 'Invalid form submission. Please try again.';
  } else {
    $completed = (string) ($_POST['completed'] ?? '');
    $evidence_note = trim((string) ($_POST['evidence_note'] ?? ''));

    if ($completed !== 'yes') {
      $completion_error = 'Please confirm the lesson is completed before submitting.';
    } elseif ($evidence_note === '') {
      $completion_error = 'Please add a short completion note or proof reference.';
    } elseif (strlen($evidence_note) > 300) {
      $completion_error = 'Completion note must be 300 characters or fewer.';
    } else {
      $completion_data[$username] = [
        'lesson' => 'SQL Injection',
        'completed_at' => date('Y-m-d H:i:s'),
        'evidence_note' => $evidence_note
      ];
      save_completion_data($completion_file, $completion_data);
      $completion_message = 'Completion saved. Keep your screenshot or proof ready for submission.';
    }
  }
}

$completion_record = $completion_data[$username] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WebGoat Lesson</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

    :root {
      --bg: #7b5bd6;
      --bg-deep: #5f43b3;
      --panel: #ffffff;
      --ink: #2c2350;
      --muted: #7d738f;
      --accent: #6c46c8;
      --accent-2: #4b2c9f;
      --shadow: rgba(40, 20, 80, 0.25);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: "Poppins", "Segoe UI", Tahoma, sans-serif;
      color: var(--ink);
      background: radial-gradient(circle at 15% 20%, #8f77ef 0%, var(--bg) 55%, #4c2d9e 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .shell {
      width: min(980px, 95vw);
      min-height: 540px;
      background: #ffffff;
      border-radius: 18px;
      box-shadow: 0 30px 60px var(--shadow);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
    }

    .art {
      position: relative;
      padding: 3rem 3rem 2.5rem;
      color: #f2edff;
      background: linear-gradient(145deg, #7c60e6, var(--bg-deep));
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .art::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 35% 20%, rgba(255, 255, 255, 0.2), transparent 55%);
      pointer-events: none;
    }

    .art h1 {
      margin: 0 0 1rem;
      font-size: clamp(1.9rem, 3vw, 2.6rem);
      font-weight: 600;
    }

    .art p {
      margin: 0;
      font-size: 0.95rem;
      letter-spacing: 0.2px;
      opacity: 0.85;
    }

    .illustration {
      width: min(360px, 80%);
      margin: 1rem 0 1.5rem;
      position: relative;
      z-index: 1;
    }

    .panel {
      padding: 3.2rem 3.2rem 2.8rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .panel h2 {
      margin: 0 0 0.2rem;
      font-size: 1.6rem;
      font-weight: 600;
    }

    .panel h3 {
      margin: 0 0 1.4rem;
      font-size: 1.05rem;
      font-weight: 500;
      color: var(--muted);
    }

    .card {
      border-radius: 14px;
      border: 1px solid #ece6f8;
      padding: 1.2rem 1.2rem 1.1rem;
      background: #f9f7ff;
      margin-bottom: 1.1rem;
    }

    .card h4 {
      margin: 0 0 0.4rem;
      font-size: 0.98rem;
    }

    .card p {
      margin: 0;
      font-size: 0.85rem;
      color: var(--muted);
    }

    .notice {
      border-radius: 10px;
      padding: 0.7rem 0.9rem;
      font-size: 0.85rem;
      margin-bottom: 1rem;
    }

    .notice--error {
      background: #fde9ef;
      color: #a2334d;
      border: 1px solid #f2b6c6;
    }

    .notice--success {
      background: #e7f6ec;
      color: #246b3a;
      border: 1px solid #bfe3c8;
    }

    .field {
      margin-bottom: 1rem;
    }

    .field label {
      font-size: 0.85rem;
      color: var(--muted);
      display: block;
      margin-bottom: 0.4rem;
    }

    .field textarea {
      width: 100%;
      border: 1px solid #e1d7f4;
      border-radius: 10px;
      padding: 0.75rem;
      font-family: inherit;
      font-size: 0.9rem;
      min-height: 120px;
      resize: vertical;
    }

    .checkbox {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.85rem;
      color: var(--muted);
    }

    .list {
      margin: 0.6rem 0 0;
      padding-left: 1.1rem;
      color: var(--muted);
      font-size: 0.85rem;
    }

    .list li {
      margin-bottom: 0.4rem;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.8rem;
      margin-top: 1.2rem;
    }

    .btn {
      border: none;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #fff;
      padding: 0.75rem 2.4rem;
      border-radius: 999px;
      font-weight: 600;
      box-shadow: 0 10px 20px rgba(88, 64, 160, 0.3);
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-outline {
      background: transparent;
      color: var(--accent);
      border: 1px solid #d8c9fb;
      box-shadow: none;
    }

    @media (max-width: 900px) {
      .shell {
        grid-template-columns: 1fr;
        min-height: auto;
      }

      .art {
        align-items: center;
        text-align: center;
      }

      .panel {
        padding: 2.5rem 2rem 2.4rem;
      }

      .btn {
        width: 100%;
      }

      .actions {
        width: 100%;
      }
    }

    @media (max-width: 640px) {
      body {
        padding: 1.25rem;
      }

      .panel {
        padding: 2rem 1.5rem 2.2rem;
      }

      .art {
        padding: 2.4rem 1.6rem 2rem;
      }
    }

    @media (max-width: 420px) {
      body {
        padding: 1rem;
      }

      .panel {
        padding: 1.8rem 1.2rem 2rem;
      }

      .actions {
        flex-direction: column;
      }

      .btn {
        padding: 0.7rem 1.6rem;
      }
    }
  </style>
</head>
<body>
  <div class="shell">
    <section class="art">
      <div>
        <h1>WebGoat Lesson</h1>
        <p>Complete the selected security challenge.</p>
      </div>

      <svg class="illustration" viewBox="0 0 400 260" role="img" aria-label="Lesson illustration">
        <defs>
          <linearGradient id="sky" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0%" stop-color="#7f6bf4" />
            <stop offset="100%" stop-color="#4d2fb2" />
          </linearGradient>
        </defs>
        <rect x="0" y="0" width="400" height="260" rx="28" fill="url(#sky)" />
        <rect x="80" y="70" width="240" height="140" rx="18" fill="#f6d26b" />
        <rect x="105" y="95" width="190" height="12" rx="6" fill="#3b237f" opacity="0.7" />
        <rect x="105" y="120" width="160" height="12" rx="6" fill="#3b237f" opacity="0.7" />
        <rect x="105" y="145" width="140" height="12" rx="6" fill="#3b237f" opacity="0.7" />
      </svg>

      <div>
        <p>Use your WebGoat environment to finish the exercise.</p>
      </div>
    </section>

    <section class="panel">
      <h2>SQL Injection</h2>
      <h3>Authentication lesson</h3>

      <div class="card">
        <h4>Objective</h4>
        <p>Demonstrate how injection can bypass login checks and how it is mitigated.</p>
        <ul class="list">
          <li>Identify where user input hits the query.</li>
          <li>Test for injectable parameters safely.</li>
          <li>Explain the impact on authentication.</li>
        </ul>
      </div>

      <div class="card">
        <h4>Submission note</h4>
        <p>Capture a screenshot or completion note as proof for the report.</p>
      </div>

      <div class="card">
        <h4>Completion evidence</h4>
        <?php if ($completion_error !== ''): ?>
          <div class="notice notice--error" role="alert">
            <?php echo htmlspecialchars($completion_error, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <?php if ($completion_message !== ''): ?>
          <div class="notice notice--success" role="status">
            <?php echo htmlspecialchars($completion_message, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <?php if ($completion_record): ?>
          <p><strong>Status:</strong> Completed on <?php echo htmlspecialchars($completion_record['completed_at'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p><strong>Evidence:</strong> <?php echo htmlspecialchars($completion_record['evidence_note'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="post" action="lesson.php">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>" />
          <div class="field checkbox">
            <input id="completed" name="completed" type="checkbox" value="yes" <?php echo $completion_record ? 'checked' : ''; ?> />
            <label for="completed">I completed the SQL Injection lesson in WebGoat.</label>
          </div>
          <div class="field">
            <label for="evidence-note">Completion note or proof reference</label>
            <textarea id="evidence-note" name="evidence_note" maxlength="300" placeholder="Example: Screenshot saved as webgoat-sqli.png"></textarea>
          </div>
          <button class="btn" type="submit">Save Completion</button>
        </form>
      </div>

      <div class="actions">
        <a class="btn" href="http://localhost:8080/WebGoat/start.mvc" target="_blank" rel="noopener">Open WebGoat Lesson</a>
        <a class="btn btn-outline" href="dashboard.php">Back to Dashboard</a>
      </div>
    </section>
  </div>
</body>
</html>
