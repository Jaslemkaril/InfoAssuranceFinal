<?php
require_once __DIR__ . '/auth.php';

ensure_session();

$error = '';
$success = '';
$username_value = '';
$code_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username_value = trim((string) ($_POST['username'] ?? ''));
  $code_value = trim((string) ($_POST['code'] ?? ''));

  if ($username_value === '' || $code_value === '') {
    $error = 'Please enter your username and verification code.';
  } else {
    $user = find_user_by_username(load_users(), $username_value);
    if (!$user) {
      $error = 'Invalid verification details.';
    } else {
      $expires = $user['otp_expires'] ?? null;
      $otp_hash = $user['otp_hash'] ?? '';

      if (!$expires || strtotime($expires) < time()) {
        $error = 'Verification code expired. Please request a new one.';
      } elseif (!password_verify($code_value, $otp_hash)) {
        $error = 'Invalid verification details.';
      } else {
        if (mark_user_verified($username_value)) {
          $success = 'Email verified. You can now sign in.';
        } else {
          $error = 'We could not verify your account. Please try again.';
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
      width: min(860px, 95vw);
      min-height: 480px;
      background: #ffffff;
      border-radius: 18px;
      box-shadow: 0 30px 60px var(--shadow);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr;
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

    .btn {
      border: none;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #fff;
      padding: 0.7rem 2.4rem;
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
        padding: 2rem 1.5rem 2.2rem;
      }
    }
  </style>
</head>
<body>
  <div class="shell">
    <section class="panel">
      <h2>Verify Email</h2>
      <h3>Enter the code sent to your email</h3>
      <form action="verify.php" method="post">
        <div class="notice notice--error<?php echo $error === '' ? ' is-hidden' : ''; ?>" role="alert">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="notice notice--success<?php echo $success === '' ? ' is-hidden' : ''; ?>" role="status">
          <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" placeholder="Username" autocomplete="username" required value="<?php echo htmlspecialchars($username_value, ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="field">
          <label for="code">Verification code</label>
          <input id="code" name="code" type="text" placeholder="6-digit code" required value="<?php echo htmlspecialchars($code_value, ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <button class="btn" type="submit">Verify</button>
      </form>
      <div class="switch">
        Back to <a href="index.php">Sign In</a>
      </div>
    </section>
  </div>
</body>
</html>
