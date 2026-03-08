# Security Audit Findings (In Progress)

## Phase 1: Backend Security Audit (PHP)

### Injection Vulnerabilities
- **SQL Injection:** No vulnerabilities found. All database queries use prepared statements or Roundcube's internal DB abstraction correctly.
- **Command Injection:** No vulnerabilities found. No usage of shell execution functions detected.
- **Code Injection:** No vulnerabilities found. No usage of `eval`, `assert`, or `unserialize` detected.
- **IMAP Injection (Low/Medium):**
    - **Location:** `snoozed_messages.php` in `check_expired_snoozes` and potentially other search-related areas.
    - **Description:** The `Message-ID` header value is used directly in an IMAP `search` command within double quotes without escaping. While `Message-ID` headers are standard, if an attacker can manipulate this header (e.g., via a specially crafted email), they could potentially inject IMAP search criteria.
    - **Recommendation:** Escape double quotes in the `message_id_header` value before using it in the IMAP `search` command.

### Access Control Logic
- **Authentication:** Verified. All web-based actions are tied to Roundcube's authenticated session.
- **Authorization (User Isolation):** Verified.
    - Database operations (SELECT, INSERT, DELETE) consistently use the authenticated `user_id`.
    - IMAP operations are performed through the current user's storage session, ensuring they cannot access or manipulate messages belonging to other accounts even if UIDs are spoofed.
- **Offline Processing:** Verified. The CLI-triggered `check_expired_snoozes` correctly iterates through records and uses individual user credentials for each IMAP connection, maintaining isolation in the background.
- **Recommendation:** No critical issues found. Ensure that any future administrative or debugging tools also strictly enforce `user_id` filtering.

### SSRF and Insecure Deserialization
- **Insecure Deserialization:** No vulnerabilities found. No usage of `unserialize()` or similar dangerous functions detected in the plugin source.
- **SSRF (Server-Side Request Forgery):**
    - **Analysis:** The plugin performs IMAP connections in the background using the `mail_host` stored in the Roundcube `users` table.
    - **Risk:** If a user can modify their own `mail_host` via Roundcube preferences, they could potentially trigger an SSRF by pointing it to an internal service.
    - **Recommendation:** This is primarily a core Roundcube configuration concern. However, the plugin could implement a whitelist of allowed mail hosts if required for higher security environments.

## Phase 2: Frontend Security Audit (JS)

### XSS Vulnerabilities
- **Analysis:**
    - UI updates use jQuery's `.text()`, `.attr()`, `.addClass()`, and `.removeClass()`, which are safe against XSS as they don't parse HTML.
    - Dialog content is constructed using `$('<div>')` and `.append()`, also using safe methods like `.text()`.
- **Finding:** No XSS vulnerabilities found in the plugin's JavaScript. The use of native Roundcube APIs for dialogs and buttons ensures consistency with core security patterns.

### CSRF Protection
- **Verified:** All AJAX requests use `rcmail.http_post()`.
- **Mechanism:** Roundcube's `http_post` automatically includes the necessary security tokens (request tokens) in the request headers, providing built-in protection against CSRF.
- **Finding:** CSRF protection is correctly implemented by leveraging the core framework.

### Client-Side Data Handling
- **Verified:** No sensitive data (passwords, PII) is stored in `localStorage`, `sessionStorage`, or cookies by the plugin.
- **Verified:** No sensitive data is exposed via `console.log` in the production-ready code.
- **Finding:** Client-side data handling is secure.

## Phase 3: Credential Vault & Database Audit

### Cryptographic Weaknesses
- **Analysis:**
    - The plugin uses `rcmail->encrypt()` and `rcmail->decrypt()` which leverage Roundcube's configured `des_key` and standard cryptographic libraries (OpenSSL/Mcrypt depending on version).
    - The "dual-compromise" principle is maintained: an attacker needs both the database (for the encrypted password) and the filesystem (for the `des_key`).
- **Finding:** Cryptographic implementation is solid and follows framework standards.

### Data Leakage Risks
- **Analysis:**
    - Database schema uses `TEXT` for `encrypted_password`.
    - Verification of record cleanup:
        - **Success path:** Record is deleted immediately after successful move.
        - **Failure path (CLI):** Record is deleted even if move fails if it's a "ghost" (no password).
        - **Manual Unsnooze path:** If `restore_message` fails (e.g. message manually deleted from Snoozed folder), the record currently persists until it would naturally expire and be handled by the CLI cron.
- **Finding:** There is a minor window of exposure where an encrypted password persists for a "ghost" message if unsnoozed manually while the message is missing from the IMAP folder.
- **Recommendation:** Update `unsnooze_message` to always delete the database record regardless of `restore_message` success, similar to the CLI logic, since a manual unsnooze request for a missing message implies the snooze is no longer valid.

### Memory Handling
- **Analysis:** Decrypted passwords are stored in local variables (`$pass`) and used only for the duration of the IMAP connection.
- **Finding:** Sensitive data exposure in memory is minimized to the necessary operations.
