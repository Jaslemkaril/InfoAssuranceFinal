<?php
require_once __DIR__ . '/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Security Overview</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap");

    :root {
      --bg: #7b5bd6;
      --bg-deep: #5f43b3;
      --ink: #2c2350;
      --muted: #7d738f;
      --accent: #6c46c8;
      --accent-2: #4b2c9f;
      --danger: #c0334e;
      --danger-bg: #fde9ef;
      --danger-border: #f2b6c6;
      --safe: #1e6e3c;
      --safe-bg: #e7f6ec;
      --safe-border: #bfe3c8;
      --warn: #7a5100;
      --warn-bg: #fff8e1;
      --warn-border: #ffe082;
      --shadow: 0 28px 60px rgba(40, 20, 80, 0.22);
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: "Source Sans 3", "Segoe UI", Tahoma, sans-serif;
      color: var(--ink);
      background: radial-gradient(circle at 15% 20%, #8f77ef 0%, var(--bg) 55%, #4c2d9e 100%);
      min-height: 100vh;
      padding: 2.5rem;
    }

    body::before, body::after {
      content: "";
      position: fixed;
      width: 420px;
      height: 420px;
      border-radius: 50%;
      z-index: 0;
      pointer-events: none;
    }
    body::before {
      top: -120px; left: -80px;
      background: radial-gradient(circle, rgba(140,118,239,0.35), transparent 65%);
    }
    body::after {
      bottom: -160px; right: -80px;
      background: radial-gradient(circle, rgba(108,70,200,0.25), transparent 65%);
    }

    .page {
      position: relative;
      z-index: 1;
      max-width: 860px;
      margin: 0 auto;
    }

    /* top bar */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .topbar-left { display: flex; align-items: center; gap: 0.8rem; }
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      color: #f2edff;
      font-size: 0.88rem;
      font-weight: 500;
      text-decoration: none;
      padding: 0.45rem 1rem;
      border-radius: 999px;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.28);
      transition: background 0.18s;
    }
    .back-btn:hover { background: rgba(255,255,255,0.28); }
    .back-btn svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .page-title {
      font-family: "Space Grotesk", sans-serif;
      color: #f2edff;
      font-size: clamp(1.5rem, 3vw, 2rem);
      font-weight: 700;
      margin: 0;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.3rem 0.75rem;
      border-radius: 999px;
      background: rgba(255,255,255,0.2);
      color: #f2edff;
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.4px;
      text-transform: uppercase;
    }

    /* cards */
    .card {
      background: rgba(255,255,255,0.96);
      border-radius: 20px;
      padding: 2rem 2.2rem;
      box-shadow: var(--shadow);
      margin-bottom: 1.4rem;
      animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both;
    }
    .card:nth-child(2) { animation-delay: 0.06s; }
    .card:nth-child(3) { animation-delay: 0.12s; }
    .card:nth-child(4) { animation-delay: 0.18s; }

    .card-header {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      margin-bottom: 1.1rem;
    }
    .icon-wrap {
      width: 44px; height: 44px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .icon-wrap svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .icon-danger  { background: var(--danger-bg); color: var(--danger); }
    .icon-warn    { background: var(--warn-bg);   color: var(--warn);   }
    .icon-safe    { background: var(--safe-bg);   color: var(--safe);   }
    .icon-info    { background: #ede8fb;           color: var(--accent); }

    .card h3 {
      font-family: "Space Grotesk", sans-serif;
      margin: 0;
      font-size: 1.15rem;
      font-weight: 600;
    }
    .card p {
      margin: 0 0 0.85rem;
      font-size: 0.95rem;
      color: #4a4060;
      line-height: 1.65;
    }
    .card p:last-child { margin-bottom: 0; }

    /* highlight blocks */
    .block {
      border-radius: 10px;
      padding: 0.9rem 1.1rem;
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 1rem;
    }
    .block:last-child { margin-bottom: 0; }
    .block-danger { background: var(--danger-bg); border-left: 3px solid var(--danger); color: #7a1f36; }
    .block-warn   { background: var(--warn-bg);   border-left: 3px solid #f0b429;      color: var(--warn); }
    .block-safe   { background: var(--safe-bg);   border-left: 3px solid #34a85a;      color: var(--safe); }

    .block strong { display: block; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.25rem; opacity: 0.75; }

    /* table */
    .table-wrap { overflow-x: auto; }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.88rem;
    }
    th {
      text-align: left;
      padding: 0.6rem 0.9rem;
      background: #f0ebff;
      color: var(--accent-2);
      font-weight: 600;
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    td {
      padding: 0.7rem 0.9rem;
      border-bottom: 1px solid #ece6f8;
      color: #3d3060;
      vertical-align: top;
    }
    tr:last-child td { border-bottom: none; }
    .tag {
      display: inline-block;
      padding: 0.15rem 0.6rem;
      border-radius: 999px;
      font-size: 0.73rem;
      font-weight: 600;
    }
    .tag-danger { background: var(--danger-bg); color: var(--danger); }
    .tag-safe   { background: var(--safe-bg);   color: var(--safe); }

    /* code */
    code {
      font-family: "Cascadia Code", "Fira Code", "Courier New", monospace;
      font-size: 0.82rem;
      background: #ede8fb;
      color: #4b2c9f;
      padding: 0.12rem 0.45rem;
      border-radius: 5px;
    }
    .code-block {
      background: #1e1640;
      color: #c9c0f8;
      border-radius: 12px;
      padding: 1rem 1.2rem;
      font-family: "Cascadia Code", "Fira Code", monospace;
      font-size: 0.82rem;
      line-height: 1.7;
      overflow-x: auto;
      margin: 0.85rem 0;
    }
    .code-block .cm { color: #6e6a8a; }
    .code-block .kw { color: #b48eff; }
    .code-block .st { color: #ffc56d; }
    .code-block .va { color: #79dcaa; }
    .code-block .hl { color: #ff7b7b; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
      body { padding: 1.25rem; }
      .card { padding: 1.5rem 1.3rem; }
      .page-title { font-size: 1.3rem; }
    }
  </style>
</head>
<body>
  <div class="page">

    <div class="topbar">
      <div class="topbar-left">
        <a class="back-btn" href="dashboard.php">
          <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
          Dashboard
        </a>
        <h1 class="page-title">Security Overview</h1>
      </div>
      <span class="badge">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Vulnerability Awareness
      </span>
    </div>

    <!-- 1. What is the vulnerability -->
    <div class="card">
      <div class="card-header">
        <div class="icon-wrap icon-danger">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3>The Vulnerability: SQL Injection &amp; Broken Authentication</h3>
      </div>

      <p>
        SQL Injection (SQLi) is a code-injection technique where an attacker inserts malicious SQL statements into an input field, manipulating the database query that the application runs. In a login form, this can allow an attacker to bypass authentication entirely — no valid password needed.
      </p>

      <div class="block block-danger">
        <strong>Example attack payload</strong>
        An attacker types the following into the username field:
      </div>
      <div class="code-block">
<span class="cm">-- Attacker input in the username field:</span>
<span class="hl">admin' OR '1'='1</span>

<span class="cm">-- The application builds this query:</span>
<span class="kw">SELECT</span> * <span class="kw">FROM</span> <span class="va">users</span>
<span class="kw">WHERE</span> username = <span class="st">'admin' OR '1'='1'</span>
  <span class="kw">AND</span> password = <span class="st">'anything'</span>;

<span class="cm">-- '1'='1' is always true → query returns the admin row → login succeeds</span>
      </div>

      <p>
        Broken Authentication occurs when session management or credential storage is weak — for example, storing plain-text passwords, using predictable tokens, or exposing detailed error messages that reveal whether a username exists.
      </p>
    </div>

    <!-- 2. What would happen without security measures -->
    <div class="card">
      <div class="card-header">
        <div class="icon-wrap icon-warn">
          <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h3>What Would Happen Without Security Measures</h3>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Missing measure</th>
              <th>Consequence</th>
              <th>Risk</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Plain-text passwords in database</td>
              <td>A single database leak exposes every user's real password — which they likely reuse on other sites.</td>
              <td><span class="tag tag-danger">Critical</span></td>
            </tr>
            <tr>
              <td>No input validation / prepared statements</td>
              <td>An attacker can inject SQL to log in as any user, dump all data, or delete records.</td>
              <td><span class="tag tag-danger">Critical</span></td>
            </tr>
            <tr>
              <td>Verbose login errors ("Username not found")</td>
              <td>Allows attackers to enumerate valid usernames and focus brute-force attempts.</td>
              <td><span class="tag tag-danger">High</span></td>
            </tr>
            <tr>
              <td>No email verification</td>
              <td>Anyone can register with a fake identity; bots can mass-register accounts.</td>
              <td><span class="tag tag-danger">High</span></td>
            </tr>
            <tr>
              <td>No CSRF token on forms</td>
              <td>A malicious site can silently submit a login or password-change request as the victim.</td>
              <td><span class="tag tag-danger">High</span></td>
            </tr>
            <tr>
              <td>Passwords stored with weak hashing (MD5/SHA-1)</td>
              <td>Rainbow-table attacks can reverse hashed passwords in seconds.</td>
              <td><span class="tag tag-danger">High</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 3. How our implementation prevents attacks -->
    <div class="card">
      <div class="card-header">
        <div class="icon-wrap icon-safe">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h3>How Our Security Features Prevent These Attacks</h3>
      </div>

      <div class="block block-safe">
        <strong>1 — bcrypt password hashing</strong>
        Passwords are hashed with PHP's <code>password_hash()</code> using the <code>PASSWORD_DEFAULT</code> algorithm (bcrypt). bcrypt is intentionally slow and adds a unique salt per password, making rainbow-table and brute-force attacks computationally impractical. Even if the data file is leaked, attackers cannot recover plain-text passwords.
      </div>

      <div class="block block-safe">
        <strong>2 — Input validation &amp; type-safe handling</strong>
        All user input is cast to <code>string</code>, trimmed, and validated before use. Credentials are checked with <code>password_verify()</code> against the stored hash — no raw SQL is ever constructed from user input, eliminating the SQL injection surface entirely.
      </div>

      <div class="block block-safe">
        <strong>3 — Generic login error messages</strong>
        Whether the username doesn't exist or the password is wrong, the application always returns the same message: <em>"Invalid username or password."</em> This prevents username enumeration attacks that rely on different responses per case.
      </div>

      <div class="block block-safe">
        <strong>4 — Email OTP verification</strong>
        New accounts require a 6-digit one-time code sent to the registered email, expiring in 10 minutes. This confirms the user owns the email address and blocks bot-registration, disposable addresses, and account takeover via fake sign-ups.
      </div>

      <div class="block block-safe">
        <strong>5 — CSRF tokens on every form</strong>
        Every state-changing form includes a cryptographically random token stored in the session. The server rejects any submission that doesn't include the correct token, preventing cross-site request forgery attacks.
      </div>

      <div class="block block-safe">
        <strong>6 — Session inactivity timeout</strong>
        Sessions are invalidated after 30 minutes of inactivity. This limits the window of opportunity if a device is left unattended or a session token is intercepted.
      </div>
    </div>

    <!-- 4. Summary -->
    <div class="card">
      <div class="card-header">
        <div class="icon-wrap icon-info">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </div>
        <h3>Summary</h3>
      </div>
      <p>
        Security is not a single feature — it is a layered approach. This application addresses the most common authentication weaknesses by combining strong password hashing, input validation, generic error responses, email verification, CSRF protection, and session management. Each layer independently reduces attack surface; together they make it significantly harder for an attacker to gain unauthorized access.
      </p>
      <p>
        The WebGoat SQL Injection lesson demonstrates in a safe environment exactly how these attacks work in practice, reinforcing why each countermeasure matters.
      </p>
    </div>

  </div>
</body>
</html>
