<?php
$smtp_port = getenv('SMTP_PORT');
$smtp_port = $smtp_port !== false && $smtp_port !== '' ? (int) $smtp_port : 587;

return [
    'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_port' => $smtp_port,
    'smtp_user' => getenv('SMTP_USER') ?: '',
    'smtp_pass' => getenv('SMTP_PASS') ?: '',
    'smtp_from_email' => getenv('SMTP_FROM_EMAIL') ?: getenv('SMTP_USER') ?: '',
    'smtp_from_name' => getenv('SMTP_FROM_NAME') ?: 'Secure Login App',
    'resend_api_key' => getenv('RESEND_API_KEY') ?: '',
    'resend_from_email' => getenv('RESEND_FROM_EMAIL') ?: 'onboarding@resend.dev',
];
