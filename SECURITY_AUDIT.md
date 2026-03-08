# Security Audit Report: Roundcube Snoozed Messages Plugin

## Overview
This report documents the security audit findings for the Roundcube Snoozed Messages plugin, conducted on March 8, 2026. The audit focused on the backend logic (PHP), frontend scripts (JS), database schema, and the credential vault for encrypted password storage.

## Audit Summary
The plugin demonstrates a strong security posture by leveraging Roundcube's native security mechanisms for authentication, CSRF protection, and database abstraction. No critical or high-risk vulnerabilities were identified that would allow for unauthorized access or compromise of the host system under standard configurations.

Several low-to-medium risk items were identified, primarily related to potential IMAP injection and minor data leakage risks in edge-case error scenarios.

---

## Detailed Findings

### Backend Security (PHP)

#### IMAP Injection (Remediated)
- **Location:** `snoozed_messages.php` in `check_expired_snoozes` and message-id search functions.
- **Description:** The `Message-ID` header value was previously used directly in an IMAP `search` command within double quotes without escaping.
- **Remediation:** Implemented a private method `_escape_imap_string()` that properly escapes double quotes and backslashes in accordance with RFC 3501. This helper is now applied to all dynamic header values used in IMAP search commands.

#### Authorization and Access Control
- **Authentication:** All plugin actions are correctly tied to Roundcube's authenticated session.
- **Isolation:** Database and IMAP operations strictly use the authenticated `user_id`, preventing cross-user data access.
- **Offline Processing:** The CLI cron job correctly handles per-user isolation by retrieving individual credentials and maintaining separate sessions.
- **Finding:** No vulnerabilities identified.

#### SSRF and Insecure Deserialization
- **Insecure Deserialization:** No usage of `unserialize()` or similar dangerous functions detected.
- **SSRF (Server-Side Request Forgery):**
    - **Analysis:** The plugin performs IMAP connections based on the `mail_host` in the Roundcube `users` table. While a user could potentially trigger an SSRF by modifying their `mail_host` (if permitted by the core configuration), this is primarily a framework-level concern.
    - **Recommendation:** Consider implementing a whitelist of allowed mail hosts for high-security environments.

---

### Frontend Security (JS)

#### XSS (Cross-Site Scripting)
- **Analysis:** UI updates and dialog construction use safe jQuery and Roundcube API methods (`.text()`, `.attr()`, etc.) that do not parse HTML strings.
- **Finding:** No XSS vulnerabilities identified.

#### CSRF (Cross-Site Request Forgery)
- **Analysis:** All AJAX requests are handled via `rcmail.http_post()`, which automatically includes Roundcube's security tokens.
- **Finding:** Correctly implemented and protected.

---

### Database and Cryptography

#### Cryptographic Implementation
- **Analysis:** Encryption and decryption utilize Roundcube's native `rcmail->encrypt()` and `rcmail->decrypt()` methods, which depend on the system-configured `des_key`.
- **Finding:** The "dual-compromise" requirement (access to both the database and the filesystem) is maintained, following best practices for the Roundcube ecosystem.

#### Data Leakage Risks
- **Analysis:** Encrypted passwords for "ghost" messages (messages that were snoozed but then manually deleted or moved outside the plugin's knowledge) can persist in the database until they naturally expire.
- **Recommendation:** Update the `unsnooze_message` function to ensure that the database record is deleted regardless of whether the IMAP `restore_message` operation succeeds, minimizing the window of exposure for encrypted credentials.

---

## OWASP Top 10 Categorization and Severity

| Category | Finding | Severity |
| :--- | :--- | :--- |
| **A01:2021-Broken Access Control** | None Identified | - |
| **A02:2021-Cryptographic Failures** | Data Leakage: Encrypted passwords for "ghost" messages persist in DB. | **Low** |
| **A03:2021-Injection** | IMAP Injection: Message-ID header properly escaped. | **Remediated** |
| **A04:2021-Insecure Design** | None Identified | - |
| **A05:2021-Security Misconfiguration** | None Identified | - |
| **A06:2021-Vulnerable and Outdated Components** | None Identified | - |
| **A07:2021-Identification and Authentication Failures** | None Identified | - |
| **A08:2021-Software and Data Integrity Failures** | None Identified | - |
| **A09:2021-Security Logging and Monitoring Failures** | None Identified | - |
| **A10:2021-Server-Side Request Forgery (SSRF)** | Potential SSRF via user-modified `mail_host` (Framework concern). | **Low** |

---

## Remediation Roadmap

The following tasks are recommended to address the identified security concerns, categorized by priority and finding severity.

### Remediated Findings (Complete)

- **R1: Mitigate IMAP Injection Risk**
    - **Finding:** Unescaped `Message-ID` header in IMAP search command.
    - **Status:** **Remediated** on March 8, 2026.
    - **Action:** Implemented `_escape_imap_string()` to handle double quotes and backslashes.

### Remaining Findings (In Progress/Pending)

- **R2: Prevent Ghost Credential Persistence**
    - **Finding:** Encrypted passwords persist for "ghost" messages after manual unsnooze failure.
    - **Severity:** **Low**
    - **Issue:** Encrypted passwords for messages that were manually deleted or moved outside the plugin's knowledge can persist in the database until they naturally expire.
    - **Action:** Modify the `unsnooze_message` logic in `snoozed_messages.php` to ensure the database record is deleted immediately after a restoration attempt, regardless of whether the message was successfully found and moved in IMAP.

- **R3: Harden against SSRF (Framework level)**
    - **Finding:** Potential SSRF via user-modified `mail_host` in Roundcube `users` table.
    - **Severity:** **Low**
    - **Issue:** While primarily a framework concern, a user could potentially trigger an SSRF by pointing their `mail_host` to an internal service if allowed by core configuration.
    - **Action:** For high-security environments, implement a configuration-based whitelist of allowed `mail_host` values within the plugin's background connection logic.

---

## Conclusion
The Roundcube Snoozed Messages plugin follows established security patterns and correctly utilizes the framework's built-in protections. Addressing the identified IMAP injection and data leakage risks will further harden the plugin against sophisticated attacks.
