<?php
require_once __DIR__ . '/auth.php';

ensure_session();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim((string) ($_POST['email'] ?? ''));

  if ($email === '') {
    $message = 'Please provide the email tied to your account.';
  } else {
    $message = 'If the account exists, a reset link will be sent.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password</title>
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
      margin: 0 0 2.2rem;
      font-size: 1.05rem;
      font-weight: 500;
      color: var(--muted);
    }

    .panel h4 {
      margin: 0 0 1.6rem;
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--ink);
    }

    .notice {
      border-radius: 10px;
      padding: 0.7rem 0.9rem;
      font-size: 0.85rem;
      margin-bottom: 1.2rem;
      background: #e7f6ec;
      color: #246b3a;
      border: 1px solid #bfe3c8;
    }

    .is-hidden {
      display: none;
    }

    .field {
      margin-bottom: 1.4rem;
    }

    .field label {
      font-size: 0.85rem;
      color: var(--muted);
      display: block;
      margin-bottom: 0.3rem;
    }

    .field input {
      width: 100%;
      border: none;
      border-bottom: 1px solid #d9d2ef;
      padding: 0.6rem 0.2rem;
      font-size: 0.95rem;
      outline: none;
      font-family: inherit;
    }

    .field input:focus {
      border-bottom-color: var(--accent);
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
      display: block;
      width: 100%;
      margin-top: 0.4rem;
    }

    .switch {
      margin-top: 1.8rem;
      font-size: 0.85rem;
      color: var(--muted);
    }

    .switch a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
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
        text-align: center;
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

      .field input {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 420px) {
      body {
        padding: 1rem;
      }

      .panel {
        padding: 1.8rem 1.2rem 2rem;
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
        <h1>Hello!</h1>
        <p>Good Morning</p>
      </div>

      <svg class="illustration" viewBox="0 0 400 260" role="img" aria-label="Night sky illustration">
        <defs>
          <linearGradient id="sky" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0%" stop-color="#7f6bf4" />
            <stop offset="100%" stop-color="#4d2fb2" />
          </linearGradient>
        </defs>
        <rect x="0" y="0" width="400" height="260" rx="28" fill="url(#sky)" />
        <circle cx="210" cy="96" r="34" fill="#f6d26b" />
        <path d="M60 170c30-40 90-42 120-10 10-22 30-34 56-34 40 0 72 34 72 74H60c-18 0-32-15-32-33 0-16 12-30 32-30z" fill="#3b237f" />
        <ellipse cx="86" cy="190" rx="46" ry="20" fill="#2c1a5e" />
        <ellipse cx="310" cy="176" rx="42" ry="18" fill="#2c1a5e" />
        <circle cx="120" cy="70" r="4" fill="#e4d8ff" />
        <circle cx="310" cy="60" r="3" fill="#e4d8ff" />
        <circle cx="260" cy="130" r="2.5" fill="#e4d8ff" />
      </svg>

      <div>
        <p>Recover your account access in minutes.</p>
        <p>We will email you a secure reset link.</p>
      </div>
    </section>

    <section class="panel">
      <h2>Reset Password</h2>
      <h3>Recover access</h3>
      <h4>Send reset link</h4>
      <form action="forgot-password.php" method="post">
        <div class="notice<?php echo $message === '' ? ' is-hidden' : ''; ?>" role="status">
          <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="field">
          <label for="email">Email address</label>
          <input id="email" name="email" type="email" placeholder="name@example.com" autocomplete="email" required />
        </div>
        <div class="field">
          <label for="username">Username (optional)</label>
          <input id="username" name="username" type="text" placeholder="Username" autocomplete="username" />
        </div>
        <button class="btn" type="submit">Send Link</button>
      </form>
      <div class="switch">
        <a href="index.php">Back to Login</a>
      </div>
    </section>
  </div>
</body>
</html>
