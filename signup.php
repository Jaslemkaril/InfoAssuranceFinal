<?php
require_once __DIR__ . '/auth.php';

ensure_session();

$error = '';
$success = '';
$values = [
  'first_name' => '',
  'middle_initial' => '',
  'last_name' => '',
  'username' => '',
  'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
    $error = 'Invalid form submission. Please try again.';
  } else {
    purge_expired_pending_users();

    $values['first_name'] = trim((string) ($_POST['first_name'] ?? ''));
    $values['middle_initial'] = trim((string) ($_POST['middle_initial'] ?? ''));
    $values['last_name'] = trim((string) ($_POST['last_name'] ?? ''));
    $values['username'] = trim((string) ($_POST['username'] ?? ''));
    $values['email'] = trim((string) ($_POST['email'] ?? ''));

    $pending_user = build_user_record($_POST, $error);
    if ($pending_user) {
      $otp_code = (string) random_int(100000, 999999);
      $otp_expires = date('Y-m-d H:i:s', time() + 600);
      $pending_user['otp_hash'] = password_hash($otp_code, PASSWORD_DEFAULT);
      $pending_user['otp_expires'] = $otp_expires;

      if (!add_pending_user($pending_user)) {
        $error = 'Account could not be created. Please try again.';
      } else {
        $mail_error = '';
        $recipient_name = trim($values['first_name'] . ' ' . $values['last_name']);
        if ($recipient_name === '') {
          $recipient_name = $values['username'];
        }

        if (send_verification_email($values['email'], $recipient_name, $otp_code, $mail_error)) {
          header('Location: verify.php?username=' . rawurlencode($values['username']));
          exit;
        } else {
          $error = 'Account created, but we could not send the verification email.';
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account</title>
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
      min-height: 560px;
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

    .is-hidden {
      display: none;
    }

    .field {
      margin-bottom: 1.2rem;
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
      padding: 0.7rem 2.4rem;
      border-radius: 6px;
      font-weight: 600;
      box-shadow: 0 10px 20px rgba(88, 64, 160, 0.3);
      cursor: pointer;
      align-self: flex-start;
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

    /* ── Loading overlay ── */
    .loading-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(44, 35, 80, 0.72);
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
      width: 52px;
      height: 52px;
      border: 4px solid rgba(255,255,255,0.25);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 0.75s linear infinite;
    }
    .loading-label {
      color: #fff;
      font-size: 0.95rem;
      font-weight: 500;
      letter-spacing: 0.2px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .shell { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
    .btn {
      transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
      letter-spacing: 0.2px;
    }
    .btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 16px 28px rgba(88,64,160,0.38); }
    .btn:active:not(:disabled) { transform: translateY(0); }
    .btn:disabled { opacity: 0.7; cursor: not-allowed; }
    .field-wrap { position: relative; }
    .toggle-pw {
      position: absolute;
      right: 0.2rem;
      bottom: 0.55rem;
      background: none;
      border: none;
      cursor: pointer;
      padding: 0.2rem;
      color: var(--muted);
      display: flex;
      align-items: center;
      line-height: 1;
    }
    .toggle-pw:hover { color: var(--accent); }
    .toggle-pw svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .notice.is-hidden { display: none !important; }
    .field input:focus { outline: none; border-bottom-color: var(--accent); transition: border-bottom-color 0.2s; }
  </style>
</head>
<body>
  <div class="shell">
    <section class="art">
      <div>
        <h1>Get Started</h1>
        <p>Create your account</p>
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

      <div></div>
    </section>

    <section class="panel">
      <h2>Create Account</h2>
      <h3>Start your journey</h3>
      <h4>Register your account</h4>
      <form action="signup.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>" />
        <div class="notice notice--error<?php echo $error === '' ? ' is-hidden' : ''; ?>" role="alert">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="notice notice--success<?php echo $success === '' ? ' is-hidden' : ''; ?>" role="status">
          <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="field">
          <label for="first-name">First name</label>
          <input id="first-name" name="first_name" type="text" placeholder="First name" autocomplete="given-name" required value="<?php echo htmlspecialchars($values['first_name'], ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="field">
          <label for="middle-initial">Middle initial (optional)</label>
          <input id="middle-initial" name="middle_initial" type="text" placeholder="M" autocomplete="additional-name" maxlength="1" value="<?php echo htmlspecialchars($values['middle_initial'], ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="field">
          <label for="last-name">Last name</label>
          <input id="last-name" name="last_name" type="text" placeholder="Last name" autocomplete="family-name" required value="<?php echo htmlspecialchars($values['last_name'], ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" placeholder="Username" autocomplete="username" required value="<?php echo htmlspecialchars($values['username'], ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="field">
          <label for="email">Email address</label>
          <input id="email" name="email" type="email" placeholder="Email" autocomplete="email" required value="<?php echo htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="field-wrap">
            <input id="password" name="password" type="password" placeholder="Password" autocomplete="new-password" required />
            <button type="button" class="toggle-pw" aria-label="Show password">
              <svg class="eye-on" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-off" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
        <div class="field">
          <label for="confirm-password">Confirm password</label>
          <div class="field-wrap">
            <input id="confirm-password" name="confirm_password" type="password" placeholder="Confirm password" autocomplete="new-password" required />
            <button type="button" class="toggle-pw" aria-label="Show password">
              <svg class="eye-on" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-off" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
        <button class="btn" type="submit">Create Account</button>
      </form>
      <div class="switch">
        Already have an account? <a href="index.php">Sign In</a>
      </div>
    </section>
  </div>
  <div class="loading-overlay" id="loadingOverlay" role="status" aria-live="polite">
    <div class="spinner"></div>
    <span class="loading-label">Please wait…</span>
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
      document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var input = btn.parentElement.querySelector('input');
          var showing = input.type === 'text';
          input.type = showing ? 'password' : 'text';
          btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
          btn.querySelector('.eye-on').style.display = showing ? '' : 'none';
          btn.querySelector('.eye-off').style.display = showing ? 'none' : '';
        });
      });
    })();
  </script>
</body>
</html>
