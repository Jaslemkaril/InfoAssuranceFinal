<?php

const USERS_FILE = __DIR__ . '/data/users.json';

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

function save_users(array $users): void {
    $payload = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents(USERS_FILE, $payload, LOCK_EX);
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

function username_is_valid(string $username): bool {
    return (bool) preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username);
}

function password_is_valid(string $password): bool {
    return strlen($password) >= 8;
}

function register_user(array $input, string &$error): bool {
    $first_name = trim((string) ($input['first_name'] ?? ''));
    $middle_initial = trim((string) ($input['middle_initial'] ?? ''));
    $last_name = trim((string) ($input['last_name'] ?? ''));
    $username = trim((string) ($input['username'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $confirm = (string) ($input['confirm_password'] ?? '');

    if ($first_name === '' || $last_name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please complete all required fields.';
        return false;
    }

    if ($middle_initial !== '' && !preg_match('/^[a-zA-Z]$/', $middle_initial)) {
        $error = 'Middle initial must be a single letter.';
        return false;
    }

    if (!username_is_valid($username)) {
        $error = 'Username must be 3-30 characters and use letters, numbers, dots, underscores, or dashes.';
        return false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
        return false;
    }

    if (!password_is_valid($password)) {
        $error = 'Password must be at least 8 characters.';
        return false;
    }

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
        return false;
    }

    $users = load_users();

    foreach ($users as $user) {
        if (normalize_username($user['username'] ?? '') === normalize_username($username)) {
            $error = 'That username is already taken.';
            return false;
        }

        if (isset($user['email']) && strtolower($user['email']) === strtolower($email)) {
            $error = 'That email is already registered.';
            return false;
        }
    }

    $full_name = $first_name;
    if ($middle_initial !== '') {
        $full_name .= ' ' . strtoupper($middle_initial) . '.';
    }
    $full_name .= ' ' . $last_name;

    $users[] = [
        'username' => $username,
        'email' => $email,
        'full_name' => $full_name,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'verified' => false,
        'otp_hash' => null,
        'otp_expires' => null,
        'created_at' => date('Y-m-d H:i:s')
    ];

    save_users($users);

    return true;
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

    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $error = 'Mailer class not found.';
        return false;
    }

    try {
        $mail = new PHPMailer\\PHPMailer\\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_user'];
        $mail->Password = $config['smtp_pass'];
        $mail->SMTPSecure = PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $config['smtp_port'];

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
