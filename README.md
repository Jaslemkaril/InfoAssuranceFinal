# Info Assurance Final - Security Notes

## Project Requirements Coverage

### Secure Authentication Measures
- Password storage uses one-way hashing (bcrypt via PHP `password_hash`).
- Input validation is applied for usernames, emails, and password length.
- Login errors are generic to avoid revealing whether a username exists.

### Why These Measures Improve Security
- Hashing prevents plaintext password exposure if the user store is leaked.
- Validation blocks obviously malformed or malicious input before it reaches sensitive logic.
- Generic errors reduce account enumeration risk by attackers.

## Vulnerability Awareness

### Example: SQL Injection / Broken Authentication
- Without validation and secure credential handling, an attacker could inject input that bypasses login checks or abuses authentication logic.
- If passwords were stored in plain text, any data leak would immediately expose all accounts.

### Impact Without Security Measures
- Accounts could be compromised quickly through leaked passwords or weak login validation.
- Attackers could enumerate users by reading detailed error messages.

### How Current Controls Help
- Hashing makes stolen passwords unusable in plain form and slows offline cracking.
- Validation rejects malformed inputs early and reduces injection surface.
- Generic errors avoid leaking account existence or system details.

## WebGoat Lesson
- Selected lesson: SQL Injection
- Evidence: recorded on the lesson page after completion.

## Hosted Link
- Add your hosted URL here before submission.

## Email Verification Setup (Gmail SMTP)
1. Enable 2-Step Verification on the sender Gmail account.
2. Create an App Password and copy it (Google only shows it once).
3. Fill in config.php with the SMTP values and App Password.
4. Install PHPMailer with Composer:
	- `composer require phpmailer/phpmailer`
