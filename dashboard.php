<?php
require_once __DIR__ . '/auth.php';

require_login();

$user = current_user();
$display_name = $user['full_name'] ?? $user['username'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Secure Access</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap");

    :root {
      --bg: #7b5bd6;
      --bg-deep: #5f43b3;
      --panel: #ffffff;
      --ink: #2c2350;
      --muted: #7d738f;
      --accent: #6c46c8;
      --accent-2: #4b2c9f;
      --accent-3: #f6d26b;
      --shadow: 0 28px 70px rgba(40, 20, 80, 0.25);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: "Source Sans 3", "Segoe UI", Tahoma, sans-serif;
      color: var(--ink);
      background: radial-gradient(circle at 15% 20%, #8f77ef 0%, var(--bg) 55%, #4c2d9e 100%);
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 2.5rem;
    }

    body::before,
    body::after {
      content: "";
      position: fixed;
      width: 420px;
      height: 420px;
      border-radius: 50%;
      z-index: 0;
      opacity: 0.4;
      filter: blur(0px);
      animation: float 12s ease-in-out infinite;
    }

    body::before {
      top: -120px;
      left: -80px;
      background: radial-gradient(circle, rgba(140, 118, 239, 0.4), transparent 65%);
    }

    body::after {
      bottom: -160px;
      right: -80px;
      background: radial-gradient(circle, rgba(108, 70, 200, 0.3), transparent 65%);
      animation-delay: 1.5s;
    }

    .shell {
      position: relative;
      width: min(1100px, 96vw);
      min-height: 600px;
      background: rgba(255, 255, 255, 0.92);
      border-radius: 28px;
      box-shadow: var(--shadow);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      backdrop-filter: blur(8px);
      z-index: 1;
    }

    .shell::after {
      content: "";
      position: absolute;
      inset: 0;
      background-image: linear-gradient(120deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
      opacity: 0.6;
      pointer-events: none;
    }

    .art {
      position: relative;
      padding: 3.2rem 3.2rem 3rem;
      color: #f2edff;
      background: linear-gradient(155deg, rgba(124, 96, 230, 0.2), rgba(95, 67, 179, 0.25));
      display: flex;
      flex-direction: column;
      gap: 1.8rem;
    }

    .art::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 35% 20%, rgba(255, 255, 255, 0.25), transparent 60%);
      pointer-events: none;
    }

    .art h1,
    .panel h2 {
      font-family: "Space Grotesk", "Segoe UI", sans-serif;
    }

    .art h1 {
      margin: 0.4rem 0 0.6rem;
      font-size: clamp(2rem, 3.2vw, 2.8rem);
      font-weight: 600;
    }

    .art p {
      margin: 0;
      font-size: 0.98rem;
      letter-spacing: 0.2px;
      color: rgba(242, 237, 255, 0.8);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.8rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.2);
      color: #f2edff;
      font-size: 0.72rem;
      letter-spacing: 0.4px;
      text-transform: uppercase;
      font-weight: 600;
    }

    .stat-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
      z-index: 1;
    }

    .stat {
      background: rgba(255, 255, 255, 0.7);
      border-radius: 16px;
      padding: 0.9rem 1rem;
      border: 1px solid rgba(255, 255, 255, 0.25);
      box-shadow: 0 12px 25px rgba(44, 35, 80, 0.18);
    }

    .stat-label {
      display: block;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      color: rgba(242, 237, 255, 0.7);
      margin-bottom: 0.35rem;
    }

    .stat-value {
      font-weight: 600;
      color: #ffffff;
    }

    .art-card {
      background: rgba(44, 35, 80, 0.9);
      color: #f8fafc;
      padding: 1.1rem 1.3rem;
      border-radius: 18px;
      box-shadow: 0 18px 32px rgba(44, 35, 80, 0.3);
    }

    .art-card h4 {
      margin: 0 0 0.35rem;
      font-size: 0.95rem;
    }

    .art-card p {
      color: rgba(242, 237, 255, 0.78);
      font-size: 0.85rem;
    }

    .illustration {
      width: min(340px, 78%);
      margin: 0.5rem 0 0.2rem;
      position: relative;
      z-index: 1;
      align-self: center;
      animation: lift 6s ease-in-out infinite;
    }

    .panel {
      padding: 3.2rem 3.2rem 2.8rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 1.6rem;
    }

    .panel h2 {
      margin: 0 0 0.3rem;
      font-size: 1.7rem;
      font-weight: 600;
    }

    .panel-sub {
      margin: 0;
      font-size: 1rem;
      color: var(--muted);
    }

    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .panel-header .btn {
      padding: 0.5rem 1.4rem;
      font-size: 0.85rem;
      box-shadow: 0 10px 18px rgba(88, 64, 160, 0.22);
    }

    .card {
      border-radius: 16px;
        border: 1px solid #e6ddfb;
        padding: 1.2rem 1.3rem;
        background: #f7f2ff;
      box-shadow: 0 16px 30px rgba(44, 35, 80, 0.08);
    }

    .card h4 {
      margin: 0 0 0.4rem;
      font-size: 1.02rem;
    }

    .card p {
      margin: 0;
      font-size: 0.88rem;
      color: var(--muted);
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.8rem;
      margin-top: 0.4rem;
    }

    .btn {
      border: none;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #fff;
      padding: 0.75rem 2.4rem;
      border-radius: 14px;
      font-weight: 600;
      box-shadow: 0 14px 28px rgba(88, 64, 160, 0.3);
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 32px rgba(88, 64, 160, 0.32);
    }

    .btn-outline {
      background: transparent;
      color: var(--accent);
      border: 1px solid #d8c9fb;
      box-shadow: none;
    }

    @keyframes float {
      0%,
      100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-14px);
      }
    }

    @keyframes lift {
      0%,
      100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-10px);
      }
    }

    @media (max-width: 900px) {
      .shell {
        grid-template-columns: 1fr;
        min-height: auto;
      }

      .art {
        align-items: center;
        text-align: center;
        padding: 2.6rem 2rem 2.4rem;
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

      .stat-grid {
        grid-template-columns: 1fr;
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

    /* ── Card entrance ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .shell { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
  </style>
</head>
<body>
  <div class="shell">
    <section class="art">
      <div class="art-header">
        <span class="badge">Session Active</span>
        <h1>Secure Access Hub</h1>
        <p>You are signed in and ready to continue your WebGoat assignment.</p>
      </div>

      <div class="stat-grid">
        <div class="stat">
          <span class="stat-label">Selected lesson</span>
          <span class="stat-value">SQL Injection</span>
        </div>
        <div class="stat">
          <span class="stat-label">Focus</span>
          <span class="stat-value">Authentication flaws</span>
        </div>
      </div>

      <svg class="illustration" viewBox="0 0 400 260" role="img" aria-label="Shield illustration">
        <defs>
          <linearGradient id="sky" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0%" stop-color="#7f6bf4" />
            <stop offset="100%" stop-color="#4d2fb2" />
          </linearGradient>
        </defs>
        <rect x="0" y="0" width="400" height="260" rx="28" fill="url(#sky)" />
        <path d="M200 55l90 35v60c0 55-38 93-90 115-52-22-90-60-90-115V90l90-35z" fill="#f6d26b" />
        <path d="M200 82l60 22v45c0 37-26 64-60 80-34-16-60-43-60-80v-45l60-22z" fill="#3b237f" opacity="0.7" />
        <circle cx="140" cy="190" r="6" fill="#e4d8ff" />
        <circle cx="260" cy="190" r="6" fill="#e4d8ff" />
      </svg>

      <div class="art-card">
        <h4>Next checkpoint</h4>
        <p>Open the lesson and complete the required tasks for submission.</p>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <div>
          <h2>Welcome, <?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></h2>
          <p class="panel-sub">Project overview and recommended next steps.</p>
        </div>
        <a class="btn btn-outline" href="logout.php">Log Out</a>
      </div>

      <div class="card">
        <h4>Selected Lesson: SQL Injection</h4>
        <p>Practice spotting and mitigating injection flaws in authentication flows.</p>
      </div>

      <div class="card">
        <h4>Security notes</h4>
        <p>Passwords are hashed with bcrypt, inputs are validated server-side, and login errors stay generic to avoid leaking account data.</p>
      </div>

      <div class="card">
        <h4>What to do next</h4>
        <p>Open the lesson and complete the activities required for submission.</p>
      </div>

      <div class="actions">
        <a class="btn" href="lesson.php">Open Lesson Details</a>
        <a class="btn btn-outline" href="security.php">Security Overview</a>
      </div>
    </section>
  </div>
</body>
</html>
