<?php
require_once __DIR__ . '/auth.php';

ensure_session();

$error = '';
$success = '';
$username_value = trim((string) ($_GET['username'] ?? ($_POST['username'] ?? '')));
$code_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
    $error = 'Invalid form submission. Please try again.';
  } else {
    $username_value = trim((string) ($_POST['username'] ?? ''));
    $code_value = trim((string) ($_POST['code'] ?? ''));

    if ($username_value === '' || $code_value === '') {
      $error = 'Please enter your username and verification code.';
    } else {
      $pending = find_pending_user_by_username(load_pending_users(), $username_value);
      if (!$pending) {
        $error = 'Invalid verification details.';
      } else {
        $expires = $pending['otp_expires'] ?? null;
        $otp_hash = $pending['otp_hash'] ?? '';

        if (!$expires || strtotime($expires) < time()) {
          $error = 'Verification code expired. Please request a new one.';
        } elseif (!password_verify($code_value, $otp_hash)) {
          $error = 'Invalid verification details.';
        } else {
          $removed = remove_pending_user_by_username($username_value);
          if (!$removed) {
            $error = 'We could not verify your account. Please try again.';
          } else {
            $final_error = '';
            if (finalize_pending_user($removed, $final_error)) {
              $success = 'Email verified. You can now sign in.';
            } else {
              $error = $final_error !== '' ? $final_error : 'We could not verify your account. Please try again.';
            }
          }
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
  <title>Verify Email</title>
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
      --shadow: rgba(40, 20, 80, 0.25);
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
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    body::before,
    body::after {
      content: "";
      position: fixed;
      width: 340px;
      height: 340px;
      border-radius: 50%;
      z-index: 0;
      opacity: 0.3;
      animation: float 12s ease-in-out infinite;
    }

    body::before {
      top: -120px;
      left: -80px;
      background: radial-gradient(circle, rgba(140, 118, 239, 0.55), transparent 65%);
    }

    body::after {
      bottom: -140px;
      right: -80px;
      background: radial-gradient(circle, rgba(108, 70, 200, 0.4), transparent 65%);
      animation-delay: 1.5s;
    }

    .shell {
      position: relative;
      width: min(520px, 95vw);
      min-height: 420px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 22px;
      box-shadow: 0 30px 60px var(--shadow);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr;
      z-index: 1;
    }

    .panel {
      padding: 3.2rem 3rem 2.8rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .panel h2 {
      margin: 0 0 0.2rem;
      font-family: "Space Grotesk", "Segoe UI", sans-serif;
      font-size: 1.8rem;
      font-weight: 600;
    }

    .panel h3 {
      margin: 0 0 2.2rem;
      font-size: 1.05rem;
      font-weight: 500;
      color: var(--muted);
    }

    .otp-hint {
      font-size: 0.85rem;
      color: var(--muted);
      margin: -1.4rem 0 1.6rem;
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
      padding: 0.8rem 0.2rem;
      font-size: 1.05rem;
      outline: none;
      font-family: inherit;
      letter-spacing: 0.2rem;
      text-align: center;
    }

    .field input::placeholder {
      letter-spacing: 0.12rem;
      text-align: center;
    }

    .btn {
      border: none;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #fff;
      padding: 0.8rem 2.4rem;
      border-radius: 999px;
      font-weight: 600;
      box-shadow: 0 10px 20px rgba(88, 64, 160, 0.3);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
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

    @media (max-width: 640px) {
      body {
        padding: 1.25rem;
      }

      .panel {
        padding: 2rem 1.6rem 2.2rem;
      }
    }

    @keyframes float {
      0%,
      100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-12px);
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
    .notice.is-hidden { display: none !important; }
    .field input:focus { outline: none; border-bottom-color: var(--accent); transition: border-bottom-color 0.2s; }
  </style>
</head>
<body>
  <div class="shell">
    <section class="panel">
      <h2>Verify Email</h2>
      <h3>Enter the code sent to your email</h3>
      <p class="otp-hint">The code expires in 10 minutes.</p>
      <form action="verify.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username_value, ENT_QUOTES, 'UTF-8'); ?>" />
        <div class="notice notice--error<?php echo $error === '' ? ' is-hidden' : ''; ?>" role="alert">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="notice notice--success<?php echo $success === '' ? ' is-hidden' : ''; ?>" role="status">
          <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="field">
          <label for="code">Verification code</label>
          <input id="code" name="code" type="text" placeholder="6-digit code" inputmode="numeric" autocomplete="one-time-code" pattern="\d{6}" maxlength="6" required value="<?php echo htmlspecialchars($code_value, ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <button class="btn" type="submit">Verify</button>
      </form>
      <div class="switch">
        Back to <a href="index.php">Sign In</a>
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
    })();
  </script>
</body>
</html>
