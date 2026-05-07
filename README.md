# IT 323 Information Assurance and Security 1 — Final Project

A secure PHP web application with user authentication and WebGoat SQL Injection lesson integration.

## Members
- Jaslim T. Karil
- Mark Christian E. Osumo
- Collen Cuento
- Kenneth Mira

**Hosted link:** https://infoassurancefinal-production.up.railway.app

---

## Project Structure

```
├── index.php            # Login page
├── signup.php           # Registration page
├── verify.php           # OTP email verification
├── forgot-password.php  # Password reset request
├── reset-password.php   # Password reset form
├── logout.php           # Session destruction
├── dashboard.php        # Main page after login
├── lesson.php           # WebGoat SQL Injection lesson + completion form
├── security.php         # Vulnerability awareness explanation page
├── auth.php             # Core authentication logic (hashing, sessions, CSRF, rate limiting)
├── config.php           # Environment variable configuration
├── data/
│   ├── users.json             # Registered user accounts
│   ├── pending_users.json     # Unverified registrations (gitignored)
│   ├── login_attempts.json    # Rate limiting records (gitignored)
│   └── lesson-completions.json # Lesson completion records (gitignored)
└── vendor/              # Composer dependencies (PHPMailer)
```

---

## Requirements Coverage

### 1. Web Application Functionality
- Login page with username and password inputs
- Registration with full name, email, and OTP email verification
- Session-based authentication — access is denied to protected pages without login

### 2. Secure Authentication Implementation

| Security Measure | Implementation |
|---|---|
| Password hashing | `password_hash($password, PASSWORD_DEFAULT)` — bcrypt with auto-generated salt |
| No plain-text passwords | Passwords are never stored or logged in readable form |
| Input validation | `filter_var()` for email, `preg_match()` for username, type-cast + trim on all inputs |
| Generic error messages | Login always returns "Invalid credentials" — never reveals if username exists |
| Rate limiting | IP-based lockout after 5 failed attempts within 15 minutes |
| CSRF protection | Random token per session validated on every POST form |
| Session timeout | Sessions invalidated after 30 minutes of inactivity |
| Secure cookies | `httponly=true`, `samesite=Strict` on all session cookies |

**Why these measures improve security:**
- Bcrypt makes stolen password hashes computationally infeasible to reverse or brute-force offline.
- Input validation rejects malformed data before it reaches any sensitive logic, eliminating injection surface.
- Generic errors prevent attackers from enumerating valid usernames (account enumeration attack).
- Rate limiting slows credential stuffing and brute-force attacks.
- CSRF tokens prevent malicious third-party sites from submitting forms on behalf of logged-in users.

### 3. WebGoat Lesson Integration
- Selected lesson: **SQL Injection — Authentication Bypass**
- After login, dashboard links to `lesson.php` which displays the lesson objective, step-by-step instructions, and a link to the WebGoat environment at `http://localhost:8080/WebGoat/start.mvc`.
- Students submit completion evidence (screenshot reference) through the form; records are saved to `data/lesson-completions.json`.

### 4. Vulnerability Awareness
See `security.php` (accessible after login via the dashboard) for a full explanation covering:
- How SQL Injection works and how `admin' OR '1'='1` bypasses authentication
- A risk table showing what would happen without each security measure
- How each implemented feature prevents the corresponding attack

---

## Deployment (Railway)

### Environment Variables
| Variable | Description |
|---|---|
| `BREVO_API_KEY` | Brevo transactional email API key |
| `BREVO_FROM_EMAIL` | Sender email address |

> Do **not** set `DATA_DIR` — leave it unset so the app uses the `data/` folder from the repository, ensuring user data persists across redeployments.

- `SMTP_FROM_EMAIL` (optional, defaults to `SMTP_USER`)
- `SMTP_FROM_NAME` (optional)
- `DATA_DIR` (optional, defaults to `./data`)

### Notes
- Railway's filesystem is ephemeral. For persistence, move users to a database.
- If you keep JSON storage, set `DATA_DIR=/tmp` in Railway for write access.

## Email Verification Setup (Gmail SMTP)
1. Enable 2-Step Verification on the sender Gmail account.
2. Create an App Password and copy it (Google only shows it once).
3. Set Railway environment variables (or fill in a local `.env` file).
4. Install PHPMailer with Composer:
	- `composer require phpmailer/phpmailer`
