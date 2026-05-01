<?php
// UI-only template; WebGoat lesson access is linked externally.
?><!DOCTYPE html>
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

      <div class="actions">
        <a class="btn" href="http://localhost:8080/WebGoat/start.mvc" target="_blank" rel="noopener">Open WebGoat Lesson</a>
        <a class="btn btn-outline" href="dashboard.php">Back to Dashboard</a>
      </div>
    </section>
  </div>
</body>
</html>
