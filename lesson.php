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
  <title>WebGoat Lesson — SQL Injection</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap");

    :root {
      --bg: #7b5bd6;
      --bg-deep: #5f43b3;
      --ink: #1e1640;
      --muted: #7d738f;
      --accent: #6c46c8;
      --accent-2: #4b2c9f;
      --shadow: rgba(40, 20, 80, 0.28);
      --step1: #6c46c8;
      --step2: #d4760a;
      --step3: #1a7a4a;
    }

    * { box-sizing: border-box; }

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

    /* ── Shell ── */
    .shell {
      width: min(1060px, 96vw);
      background: #ffffff;
      border-radius: 22px;
      box-shadow: 0 32px 72px var(--shadow);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1.15fr;
      animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
    }

    /* ── Left art panel ── */
    .art {
      position: relative;
      padding: 3rem 2.6rem 2.8rem;
      color: #f0eaff;
      background: linear-gradient(155deg, #7c60e6 0%, #5136ab 60%, #3b2180 100%);
      display: flex;
      flex-direction: column;
      gap: 1.8rem;
      overflow: hidden;
    }

    /* decorative blobs */
    .art::before {
      content: "";
      position: absolute;
      top: -60px; right: -60px;
      width: 260px; height: 260px;
      border-radius: 50%;
      background: rgba(255,255,255,0.07);
      pointer-events: none;
    }
    .art::after {
      content: "";
      position: absolute;
      bottom: -80px; left: -50px;
      width: 300px; height: 300px;
      border-radius: 50%;
      background: rgba(255,255,255,0.05);
      pointer-events: none;
    }

    .art-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(255,255,255,0.14);
      border: 1px solid rgba(255,255,255,0.22);
      border-radius: 999px;
      padding: 0.35rem 0.9rem;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      width: fit-content;
    }

    .art h1 {
      margin: 0;
      font-size: clamp(1.7rem, 2.6vw, 2.4rem);
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: -0.3px;
    }

    .art-sub {
      margin: 0;
      font-size: 0.9rem;
      opacity: 0.8;
      line-height: 1.5;
    }

    /* terminal window illustration */
    .terminal {
      background: #1a1230;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 16px 40px rgba(0,0,0,0.4);
      position: relative;
      z-index: 1;
    }

    .terminal-bar {
      background: #2d2050;
      padding: 0.55rem 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    .dot {
      width: 11px; height: 11px;
      border-radius: 50%;
    }
    .dot-r { background: #ff5f57; }
    .dot-y { background: #febc2e; }
    .dot-g { background: #28c840; }

    .terminal-title {
      font-size: 0.72rem;
      color: rgba(255,255,255,0.45);
      font-family: "JetBrains Mono", monospace;
      margin-left: auto;
    }

    .terminal-body {
      padding: 1rem 1.1rem 1.2rem;
      font-family: "JetBrains Mono", monospace;
      font-size: 0.72rem;
      line-height: 1.75;
    }

    .t-comment { color: #6a5f8a; }
    .t-sql     { color: #c9a0ff; }
    .t-inject  { color: #ff6b9d; font-weight: 500; }
    .t-result  { color: #5effa0; }
    .t-cursor  {
      display: inline-block;
      width: 8px; height: 13px;
      background: #c9a0ff;
      vertical-align: middle;
      animation: blink 1.1s step-end infinite;
    }

    @keyframes blink { 50% { opacity: 0; } }

    .art-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      position: relative;
      z-index: 1;
    }

    .tag {
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.4px;
      padding: 0.28rem 0.72rem;
      border-radius: 999px;
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.18);
    }

    /* ── Right panel ── */
    .panel {
      padding: 3rem 3.2rem 2.8rem;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

    .panel-header {
      margin-bottom: 1.8rem;
    }

    .panel-header h2 {
      margin: 0 0 0.2rem;
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--ink);
    }

    .panel-header p {
      margin: 0;
      font-size: 0.88rem;
      color: var(--muted);
    }

    /* ── Step cards ── */
    .step-card {
      border-radius: 16px;
      border: 1px solid #ede7fb;
      padding: 1.3rem 1.4rem;
      background: #faf8ff;
      margin-bottom: 1.1rem;
      position: relative;
      overflow: hidden;
      transition: box-shadow 0.2s;
    }

    .step-card:hover {
      box-shadow: 0 6px 20px rgba(100,70,200,0.1);
    }

    .step-card::before {
      content: "";
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 4px;
      border-radius: 4px 0 0 4px;
    }

    .step-card.step-objective::before  { background: var(--step1); }
    .step-card.step-screenshot::before { background: var(--step2); }
    .step-card.step-form::before       { background: var(--step3); }

    .step-header {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      margin-bottom: 0.75rem;
    }

    .step-num {
      width: 26px; height: 26px;
      border-radius: 50%;
      font-size: 0.72rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      color: #fff;
    }

    .step-objective  .step-num { background: var(--step1); }
    .step-screenshot .step-num { background: var(--step2); }
    .step-form       .step-num { background: var(--step3); }

    .step-title {
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--ink);
    }

    /* objective list */
    .obj-list {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
    }

    .obj-list li {
      display: flex;
      align-items: flex-start;
      gap: 0.55rem;
      font-size: 0.84rem;
      color: #3e3460;
      line-height: 1.4;
    }

    .obj-list li::before {
      content: "✓";
      color: var(--step1);
      font-weight: 700;
      font-size: 0.82rem;
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* screenshot card */
    .screenshot-body {
      display: flex;
      align-items: flex-start;
      gap: 0.8rem;
    }

    .screenshot-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      background: rgba(212,118,10,0.12);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .screenshot-text {
      font-size: 0.84rem;
      color: #5a4010;
      line-height: 1.5;
    }

    .screenshot-text strong {
      display: block;
      font-weight: 600;
      margin-bottom: 0.2rem;
    }

    /* already completed banner */
    .completed-badge {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      background: #e6f9ee;
      border: 1px solid #a8dfc0;
      border-radius: 10px;
      padding: 0.65rem 0.9rem;
      font-size: 0.82rem;
      color: #1a7a4a;
      font-weight: 600;
      margin-bottom: 0.9rem;
    }

    .completed-badge svg { flex-shrink: 0; }

    .completed-meta {
      background: #f4fdf7;
      border: 1px solid #cce8d8;
      border-radius: 10px;
      padding: 0.7rem 0.9rem;
      font-size: 0.82rem;
      color: #2e5e3a;
      margin-bottom: 0.9rem;
      line-height: 1.6;
    }

    /* notices */
    .notice {
      border-radius: 10px;
      padding: 0.7rem 0.9rem;
      font-size: 0.83rem;
      margin-bottom: 0.9rem;
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
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

    /* form elements */
    .field { margin-bottom: 0.95rem; }

    .field label {
      font-size: 0.82rem;
      font-weight: 500;
      color: var(--ink);
      display: block;
      margin-bottom: 0.4rem;
    }

    .field textarea {
      width: 100%;
      border: 1.5px solid #e0d7f4;
      border-radius: 10px;
      padding: 0.75rem;
      font-family: inherit;
      font-size: 0.87rem;
      min-height: 100px;
      resize: vertical;
      background: #fff;
      color: var(--ink);
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .field textarea:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(108,70,200,0.1);
    }

    .checkbox-row {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.65rem 0.8rem;
      background: #f0ebff;
      border: 1.5px solid #d8c9fb;
      border-radius: 10px;
      margin-bottom: 0.95rem;
      cursor: pointer;
      transition: background 0.15s;
    }

    .checkbox-row:hover { background: #e8e0ff; }

    .checkbox-row input[type="checkbox"] {
      accent-color: var(--accent);
      width: 16px;
      height: 16px;
      flex-shrink: 0;
      cursor: pointer;
    }

    .checkbox-row label {
      font-size: 0.84rem;
      color: var(--ink);
      font-weight: 500;
      cursor: pointer;
      line-height: 1.4;
    }

    /* ── Buttons ── */
    .btn {
      border: none;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #fff;
      padding: 0.78rem 2.2rem;
      border-radius: 999px;
      font-family: inherit;
      font-size: 0.9rem;
      font-weight: 600;
      letter-spacing: 0.2px;
      box-shadow: 0 10px 22px rgba(88,64,160,0.3);
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
    }

    .btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 16px 30px rgba(88,64,160,0.4);
    }

    .btn:active:not(:disabled) { transform: translateY(0); }
    .btn:disabled { opacity: 0.65; cursor: not-allowed; }

    .btn-outline {
      background: transparent;
      color: var(--accent);
      border: 1.5px solid #d0bffe;
      box-shadow: none;
    }

    .btn-outline:hover:not(:disabled) {
      background: #f4efff;
      box-shadow: none;
    }

    .btn-sm {
      padding: 0.6rem 1.4rem;
      font-size: 0.84rem;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.8rem;
      margin-top: 1.4rem;
      padding-top: 1.2rem;
      border-top: 1px solid #ede7fb;
    }

    /* ── Loading overlay ── */
    .loading-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(30, 22, 64, 0.75);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 1.2rem;
    }

    .loading-overlay.active { display: flex; }

    .spinner {
      width: 52px; height: 52px;
      border: 4px solid rgba(255,255,255,0.22);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 0.75s linear infinite;
    }

    .loading-label {
      color: #fff;
      font-size: 0.92rem;
      font-weight: 500;
      letter-spacing: 0.2px;
    }

    @keyframes spin    { to { transform: rotate(360deg); } }
    @keyframes fadeUp  {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Responsive ── */
    @media (max-width: 860px) {
      .shell {
        grid-template-columns: 1fr;
      }

      .art {
        padding: 2.6rem 2rem 2.2rem;
      }

      .panel {
        padding: 2.4rem 2rem 2.4rem;
      }

      .btn { width: 100%; }
      .actions { flex-direction: column; }
    }

    @media (max-width: 540px) {
      body { padding: 1rem; }
      .art  { padding: 2rem 1.5rem 1.8rem; }
      .panel { padding: 2rem 1.5rem 2rem; }
    }
  </style>
</head>
<body>
  <div class="shell">

    <!-- ── Left art panel ── -->
    <section class="art">
      <div>
        <span class="art-badge">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><circle cx="5" cy="5" r="4" fill="#a78bfa"/></svg>
          WebGoat &nbsp;·&nbsp; Lesson 1
        </span>
      </div>

      <div>
        <h1>SQL Injection<br>Attack Lab</h1>
        <p class="art-sub">Bypass authentication using crafted SQL payloads and understand how parameterized queries prevent it.</p>
      </div>

      <!-- Terminal illustration -->
      <div class="terminal">
        <div class="terminal-bar">
          <span class="dot dot-r"></span>
          <span class="dot dot-y"></span>
          <span class="dot dot-g"></span>
          <span class="terminal-title">sqli_demo.sql</span>
        </div>
        <div class="terminal-body">
          <span class="t-comment">-- normal query</span><br>
          <span class="t-sql">SELECT</span> * <span class="t-sql">FROM</span> users<br>
          <span class="t-sql">WHERE</span> user = <span class="t-inject">'admin'</span><br>
          &nbsp;&nbsp;<span class="t-sql">AND</span> pass = <span class="t-inject">'secret'</span>;<br>
          <br>
          <span class="t-comment">-- injected payload</span><br>
          <span class="t-sql">SELECT</span> * <span class="t-sql">FROM</span> users<br>
          <span class="t-sql">WHERE</span> user = <span class="t-inject">'admin' <span style="color:#ff9e6b">OR '1'='1'</span> --</span><br>
          &nbsp;&nbsp;<span class="t-sql">AND</span> pass = <span class="t-inject">''</span>;<br>
          <br>
          <span class="t-result">-- auth bypass successful ✓</span><br>
          <span class="t-cursor"></span>
        </div>
      </div>

      <div class="art-tags">
        <span class="tag">OWASP A03:2021</span>
        <span class="tag">Injection</span>
        <span class="tag">Authentication</span>
        <span class="tag">WebGoat</span>
      </div>
    </section>

    <!-- ── Right panel ── -->
    <section class="panel">
      <div class="panel-header">
        <h2>SQL Injection</h2>
        <p>Authentication bypass &mdash; complete all four steps below</p>
      </div>

      <!-- Step 1: Install WebGoat -->
      <div class="step-card step-objective">
        <div class="step-header">
          <span class="step-num">1</span>
          <span class="step-title">Install &amp; Run WebGoat</span>
        </div>
        <p style="font-size:0.88rem;color:#4a4060;margin:0 0 0.75rem;">WebGoat is a deliberately insecure Java application by OWASP used to practice web security. Follow these steps to run it locally:</p>
        <ol class="obj-list" style="padding-left:1.2rem;">
          <li>Make sure <strong>Java 17+</strong> is installed on your machine.<br>
            <code style="font-size:0.78rem;color:var(--accent);background:#ede8ff;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:4px">java -version</code>
          </li>
          <li>Click <strong>Open WebGoat</strong> below to go to the GitHub Releases page, then download the latest <strong>.jar</strong> file (e.g. <code style="font-size:0.78rem;color:var(--accent);background:#ede8ff;padding:2px 7px;border-radius:4px">webgoat-2023.8.jar</code>).</li>
          <li>Open a terminal in the download folder and run:<br>
            <code style="font-size:0.78rem;color:var(--accent);background:#ede8ff;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:4px">java -jar webgoat-2023.8.jar</code>
          </li>
          <li>Once started, open your browser and visit:<br>
            <code style="font-size:0.78rem;color:var(--accent);background:#ede8ff;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:4px">http://localhost:8080/WebGoat</code>
          </li>
          <li>Register a new account on WebGoat and log in.</li>
        </ol>
      </div>

      <!-- Step 2: Do the lesson -->
      <div class="step-card" style="border-left:3px solid var(--step1);">
        <div class="step-header">
          <span class="step-num" style="background:var(--step1);">2</span>
          <span class="step-title">Complete the SQL Injection Lesson</span>
        </div>
        <p style="font-size:0.88rem;color:#4a4060;margin:0 0 0.75rem;">Inside WebGoat, navigate to the specific lesson this project is based on:</p>
        <ol class="obj-list" style="padding-left:1.2rem;">
          <li>In the left sidebar, click <strong>SQL Injection (intro)</strong>.</li>
          <li>Work through the lesson pages until you reach <strong>"Authentication Bypass"</strong>.</li>
          <li>In the login form, enter the payload below into the username field and any text for the password:<br>
            <code style="font-size:0.78rem;color:var(--accent);background:#ede8ff;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:4px">admin' OR '1'='1' --</code>
          </li>
          <li>Observe that the login succeeds without a valid password — this is the SQL Injection bypass.</li>
          <li>Read the lesson explanation on why <strong>parameterized queries / prepared statements</strong> prevent this attack.</li>
        </ol>
      </div>

      <!-- Step 3: Screenshot reminder -->
      <div class="step-card step-screenshot">
        <div class="step-header">
          <span class="step-num">3</span>
          <span class="step-title">Capture Your Proof</span>
        </div>
        <div class="screenshot-body">
          <div class="screenshot-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4760a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M3 9h1M3 15h1M20 9h1M20 15h1M9 3v1M15 3v1M9 20v1M15 20v1"/>
            </svg>
          </div>
          <div class="screenshot-text">
            <strong>Take a screenshot of the completed lesson page</strong>
            Save it as <code style="font-size:0.78rem;color:#7a4b00;background:#fff3e0;padding:1px 5px;border-radius:4px">webgoat-sqli.png</code> and keep it ready for your report. Your completion note below should reference it.
          </div>
        </div>
      </div>

      <!-- Step 4: Completion form -->
      <div class="step-card step-form">
        <div class="step-header">
          <span class="step-num">4</span>
          <span class="step-title">Submit Completion Evidence</span>
        </div>

        <?php if ($completion_record): ?>
          <div class="completed-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 6L9 17l-5-5"/>
            </svg>
            Lesson marked as completed
          </div>
          <div class="completed-meta">
            <strong>Submitted:</strong> <?php echo htmlspecialchars($completion_record['completed_at'], ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Evidence note:</strong> <?php echo htmlspecialchars($completion_record['evidence_note'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <?php if ($completion_error !== ''): ?>
          <div class="notice notice--error" role="alert">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php echo htmlspecialchars($completion_error, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <?php if ($completion_message !== ''): ?>
          <div class="notice notice--success" role="status">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><path d="M20 6L9 17l-5-5"/></svg>
            <?php echo htmlspecialchars($completion_message, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="lesson.php">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>" />

          <label class="checkbox-row" for="completed">
            <input id="completed" name="completed" type="checkbox" value="yes" <?php echo $completion_record ? 'checked' : ''; ?> />
            <span>I completed the SQL Injection lesson in WebGoat.</span>
          </label>

          <div class="field">
            <label for="evidence-note">Completion note or file reference</label>
            <textarea id="evidence-note" name="evidence_note" maxlength="300" placeholder="e.g. Screenshot saved as webgoat-sqli.png — bypassed login using admin' OR '1'='1 --"><?php echo $completion_record ? htmlspecialchars($completion_record['evidence_note'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
          </div>

          <button class="btn btn-sm" type="submit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <?php echo $completion_record ? 'Update Completion' : 'Save Completion'; ?>
          </button>
        </form>
      </div>

      <!-- Bottom actions -->
      <div class="actions">
        <a class="btn" href="https://github.com/WebGoat/WebGoat/releases" target="_blank" rel="noopener">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Open WebGoat
        </a>
        <a class="btn btn-outline" href="dashboard.php">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          Back to Dashboard
        </a>
      </div>
    </section>
  </div>

  <!-- Loading overlay -->
  <div class="loading-overlay" id="loadingOverlay" role="status" aria-live="polite">
    <div class="spinner"></div>
    <span class="loading-label">Saving…</span>
  </div>

  <script>
    (function () {
      var overlay = document.getElementById('loadingOverlay');
      document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
          if (form.checkValidity()) {
            overlay.classList.add('active');
          }
        });
      });
    })();
  </script>
</body>
</html>
