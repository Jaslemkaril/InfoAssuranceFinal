<?php

$data_dir = getenv('DATA_DIR');
if ($data_dir === false || trim($data_dir) === '') {
    $data_dir = __DIR__ . '/data';
}

define('DATA_DIR', rtrim($data_dir, '/\\'));
define('USERS_FILE', DATA_DIR . '/users.json');
define('PENDING_USERS_FILE', DATA_DIR . '/pending_users.json');

function ensure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function load_users(): array {
    if (!file_exists(USERS_FILE)) {
        return [];
    }

    $raw = file_get_contents(USERS_FILE);
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function load_pending_users(): array {
    if (!file_exists(PENDING_USERS_FILE)) {
        return [];
    }

    $raw = file_get_contents(PENDING_USERS_FILE);
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function save_users(array $users): void {
    $payload = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents(USERS_FILE, $payload, LOCK_EX);
}

function save_pending_users(array $users): void {
    $payload = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents(PENDING_USERS_FILE, $payload, LOCK_EX);
}

function update_user_by_username(string $username, array $updates): bool {
    $users = load_users();
    $username = normalize_username($username);

    foreach ($users as $index => $user) {
        if (isset($user['username']) && normalize_username($user['username']) === $username) {
            $users[$index] = array_merge($user, $updates);
            save_users($users);
            return true;
        }
    }

    return false;
}

function normalize_username(string $username): string {
    return strtolower(trim($username));
}

function find_user_by_username(array $users, string $username): ?array {
    $username = normalize_username($username);

    foreach ($users as $user) {
        if (isset($user['username']) && normalize_username($user['username']) === $username) {
            return $user;
        }
    }

    return null;
}

function find_user_by_email(array $users, string $email): ?array {
    $email = strtolower(trim($email));

    foreach ($users as $user) {
        if (isset($user['email']) && strtolower($user['email']) === $email) {
            return $user;
        }
    }

    return null;
}

function find_pending_user_by_username(array $users, string $username): ?array {
    $username = normalize_username($username);

    foreach ($users as $user) {
        if (isset($user['username']) && normalize_username($user['username']) === $username) {
            return $user;
        }
    }

    return null;
}

function username_is_valid(string $username): bool {
    return (bool) preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username);
}

function password_is_valid(string $password): bool {
    return strlen($password) >= 8;
}

function register_user(array $input, string &$error): bool {
    $user = build_user_record($input, $error);
    if (!$user) {
        return false;
    }

    $users = load_users();
    $users[] = $user;
    save_users($users);

    return true;
}

function build_user_record(array $input, string &$error): ?array {
    $first_name = trim((string) ($input['first_name'] ?? ''));
    $middle_initial = trim((string) ($input['middle_initial'] ?? ''));
    $last_name = trim((string) ($input['last_name'] ?? ''));
    $username = trim((string) ($input['username'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $confirm = (string) ($input['confirm_password'] ?? '');

    if ($first_name === '' || $last_name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please complete all required fields.';
        return null;
    }

    if ($middle_initial !== '' && !preg_match('/^[a-zA-Z]$/', $middle_initial)) {
        $error = 'Middle initial must be a single letter.';
        return null;
    }

    if (!username_is_valid($username)) {
        $error = 'Username must be 3-30 characters and use letters, numbers, dots, underscores, or dashes.';
        return null;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
        return null;
    }

    if (!password_is_valid($password)) {
        $error = 'Password must be at least 8 characters.';
        return null;
    }

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
        return null;
    }

    $users = load_users();
    $pending_users = load_pending_users();

    foreach (array_merge($users, $pending_users) as $user) {
        if (normalize_username($user['username'] ?? '') === normalize_username($username)) {
            $error = 'That username is already taken.';
            return null;
        }

        if (isset($user['email']) && strtolower($user['email']) === strtolower($email)) {
            $error = 'That email is already registered.';
            return null;
        }
    }

    $full_name = $first_name;
    if ($middle_initial !== '') {
        $full_name .= ' ' . strtoupper($middle_initial) . '.';
    }
    $full_name .= ' ' . $last_name;

    return [
        'username' => $username,
        'email' => $email,
        'full_name' => $full_name,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'verified' => false,
        'otp_hash' => null,
        'otp_expires' => null,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

function add_pending_user(array $user): bool {
    $pending = load_pending_users();
    $pending[] = $user;
    save_pending_users($pending);
    return true;
}

function remove_pending_user_by_username(string $username): ?array {
    $pending = load_pending_users();
    $username = normalize_username($username);
    $found = null;

    foreach ($pending as $index => $user) {
        if (isset($user['username']) && normalize_username($user['username']) === $username) {
            $found = $user;
            unset($pending[$index]);
            break;
        }
    }

    if ($found !== null) {
        save_pending_users(array_values($pending));
    }

    return $found;
}

function resend_pending_verification(string $username, string &$error): bool {
    $pending_users = load_pending_users();
    $pending_user = find_pending_user_by_username($pending_users, $username);
    if (!$pending_user) {
        $error = 'We could not find that pending account.';
        return false;
    }

    $otp_code = (string) random_int(100000, 999999);
    $pending_user['otp_hash'] = password_hash($otp_code, PASSWORD_DEFAULT);
    $pending_user['otp_expires'] = date('Y-m-d H:i:s', time() + 600);

    foreach ($pending_users as $index => $user) {
        if (isset($user['username']) && normalize_username($user['username']) === normalize_username($username)) {
            $pending_users[$index] = $pending_user;
            break;
        }
    }

    save_pending_users($pending_users);

    $recipient_name = $pending_user['full_name'] ?? $pending_user['username'] ?? '';
    if ($recipient_name === '') {
        $recipient_name = 'Student';
    }

    return send_verification_email($pending_user['email'] ?? '', $recipient_name, $otp_code, $error);
}

function finalize_pending_user(array $pending_user, string &$error): bool {
    $users = load_users();
    foreach ($users as $user) {
        if (normalize_username($user['username'] ?? '') === normalize_username($pending_user['username'] ?? '')) {
            $error = 'That username is already taken.';
            return false;
        }

        if (isset($user['email']) && strtolower($user['email']) === strtolower($pending_user['email'] ?? '')) {
            $error = 'That email is already registered.';
            return false;
        }
    }

    $pending_user['verified'] = true;
    $pending_user['otp_hash'] = null;
    $pending_user['otp_expires'] = null;

    $users[] = $pending_user;
    save_users($users);
    return true;
}

function set_password_reset_token(string $username, string $reset_hash, string $reset_expires): bool {
    return update_user_by_username($username, [
        'reset_hash' => $reset_hash,
        'reset_expires' => $reset_expires
    ]);
}

function clear_password_reset_token(string $username): bool {
    return update_user_by_username($username, [
        'reset_hash' => null,
        'reset_expires' => null
    ]);
}

function update_user_password(string $username, string $password_hash): bool {
    return update_user_by_username($username, [
        'password_hash' => $password_hash,
        'reset_hash' => null,
        'reset_expires' => null
    ]);
}

function find_user_by_reset_token(string $token): ?array {
    $users = load_users();

    foreach ($users as $user) {
        $reset_hash = $user['reset_hash'] ?? '';
        $reset_expires = $user['reset_expires'] ?? null;

        if ($reset_hash === '' || !$reset_expires) {
            continue;
        }

        if (strtotime($reset_expires) < time()) {
            continue;
        }

        if (password_verify($token, $reset_hash)) {
            return $user;
        }
    }

    return null;
}

function authenticate_user(string $username, string $password, string &$error): ?array {
    $users = load_users();
    $user = find_user_by_username($users, $username);

    if (!$user) {
        $error = 'Invalid username or password.';
        return null;
    }

    if (!password_verify($password, $user['password_hash'] ?? '')) {
        $error = 'Invalid username or password.';
        return null;
    }

    if (empty($user['verified'])) {
        $error = 'Please verify your email before logging in.';
        return null;
    }

    return $user;
}

function set_user_verification_token(string $username, string $otp_hash, string $otp_expires): bool {
    return update_user_by_username($username, [
        'verified' => false,
        'otp_hash' => $otp_hash,
        'otp_expires' => $otp_expires
    ]);
}

function mark_user_verified(string $username): bool {
    return update_user_by_username($username, [
        'verified' => true,
        'otp_hash' => null,
        'otp_expires' => null
    ]);
}

function send_verification_email(string $to_email, string $to_name, string $otp, string &$error): bool {
    $config_path = __DIR__ . '/config.php';
    if (!file_exists($config_path)) {
        $error = 'Email configuration is missing.';
        return false;
    }

    $config = require $config_path;
    if (!is_array($config)) {
        $error = 'Email configuration is invalid.';
        return false;
    }

    $required = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'];
    foreach ($required as $key) {
        if (empty($config[$key])) {
            $error = 'Email configuration is incomplete.';
            return false;
        }
    }

    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        $error = 'Mailer dependency is not installed.';
        return false;
    }

    require_once $autoload;

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $error = 'Mailer class not found.';
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_user'];
        $mail->Password = $config['smtp_pass'];
        $mail->Timeout = 10;
        $mail->Port = (int) $config['smtp_port'];
        $mail->SMTPSecure = $mail->Port === 465
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($config['smtp_from_email'], $config['smtp_from_name']);
        $mail->addAddress($to_email, $to_name);

        $mail->Subject = 'Your verification code';
        $mail->Body = "Your verification code is: {$otp}. It expires in 10 minutes.";
        $mail->AltBody = "Your verification code is: {$otp}. It expires in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $exception) {
        $error = 'Email could not be sent.';
        return false;
    }
}

function send_password_reset_email(string $to_email, string $to_name, string $reset_link, string &$error): bool {
    $config_path = __DIR__ . '/config.php';
    if (!file_exists($config_path)) {
        $error = 'Email configuration is missing.';
        return false;
    }

    $config = require $config_path;
    if (!is_array($config)) {
        $error = 'Email configuration is invalid.';
        return false;
    }

    $required = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'];
    foreach ($required as $key) {
        if (empty($config[$key])) {
            $error = 'Email configuration is incomplete.';
            return false;
        }
    }

    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        $error = 'Mailer dependency is not installed.';
        return false;
    }

    require_once $autoload;

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $error = 'Mailer class not found.';
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_user'];
        $mail->Password = $config['smtp_pass'];
        $mail->Timeout = 10;
        $mail->Port = (int) $config['smtp_port'];
        $mail->SMTPSecure = $mail->Port === 465
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($config['smtp_from_email'], $config['smtp_from_name']);
        $mail->addAddress($to_email, $to_name);

        $mail->Subject = 'Reset your password';
        $mail->Body = "Use this link to reset your password: {$reset_link}. This link expires in 10 minutes.";
        $mail->AltBody = "Use this link to reset your password: {$reset_link}. This link expires in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $exception) {
        $error = 'Email could not be sent.';
        return false;
    }
}

function require_login(): void {
    ensure_session();

    if (empty($_SESSION['user'])) {
        header('Location: index.php');
        exit;
    }
}

function current_user(): ?array {
    ensure_session();

    return $_SESSION['user'] ?? null;
}
