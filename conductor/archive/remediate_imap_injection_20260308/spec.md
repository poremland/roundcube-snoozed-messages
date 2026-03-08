# Specification: Address Medium Security Findings (IMAP Injection)

## Overview
Remediate the identified medium-severity IMAP injection vulnerability in the `snoozed_messages` plugin and update all relevant project documentation to reflect the fix.

## Scope
- **Finding R1:** Mitigate IMAP Injection Risk (Medium Severity).
- **File:** `snoozed_messages.php`.
- **Documentation:** `SECURITY_AUDIT.md`, `conductor/product.md`.

## Functional Requirements
- **Helper Function:**
    - Implement a private method `_escape_imap_string($string)` within the `snoozed_messages` class.
    - This method must escape double quotes (`"`) and potentially other control characters as per IMAP protocol requirements for quoted strings.
- **Implementation:**
    - Apply `_escape_imap_string()` to the `Message-ID` header value extracted from the database before it is used in the `rcube_storage::search()` call within `check_expired_snoozes()`.
    - Ensure any other locations using dynamic headers in IMAP searches also use this helper.
- **Documentation Update:**
    - Update `SECURITY_AUDIT.md` to indicate that the medium finding has been remediated.
    - Synchronize `conductor/product.md` if necessary based on the final implementation.

## Non-Functional Requirements
- **Robustness:** The escaping should handle complex `Message-ID` formats correctly without breaking legitimate searches.
- **Performance:** Minimal overhead during background re-delivery tasks.
- **Framework Compliance:** Strictly use Roundcube Plugin APIs and maintain local class structures.

## Acceptance Criteria
- **Reproduction Test Passes:** The `SecurityAuditTest.php` which reproduces the potential injection should pass after the fix is applied.
- **No Regressions:** Existing message re-delivery functionality must remain operational for standard emails.
- **Test Suite Pass:** All tests in the `tests/` directory must pass.
- **Documentation Synchronized:** Security reports and product definitions are up to date.

## Out of Scope
- Addressing Low-severity findings (Ghost Credential Persistence, SSRF).
- Modifying core Roundcube IMAP logic.
