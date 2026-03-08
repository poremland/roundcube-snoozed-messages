# Specification: Offline Redelivery with Encrypted Credential Vault

## Overview
Enable automated unsnoozing of messages even when users are logged out of Roundcube. This requires the plugin to store encrypted IMAP credentials to allow background cron tasks to connect to the mail server on behalf of the user.

## Requirements
- **Secure Storage:** Store encrypted user credentials in the plugin's own database table (`snoozed_messages`) to avoid modifying core Roundcube tables.
- **Strong Encryption:** Use Roundcube's internal encryption mechanisms (or a robust alternative) with a key sourced from the local filesystem (`config.inc.php`) to ensure dual-compromise security.
- **Seamless Capture:** Automatically capture and encrypt the user's IMAP password during active sessions (either at login or when a snooze action is performed).
- **Offline Processing:** Update the background task (`check_expired_snoozes`) to identify and process expired snoozes for all users, initiating temporary IMAP connections as needed.
- **Privacy:** Ensure that credentials are only used for the intended move operation and are purged once the snooze record is deleted.

## Implementation Details
- **Schema:** Add an `encrypted_password` column to the `snoozed_messages` table.
- **Encryption:** Utilize `rcube::get_instance()->encrypt()` and `decrypt()` which leverage the `des_key` from the Roundcube configuration.
- **Workflow:** 
    1. User snoozes a message while logged in.
    2. Plugin captures the current IMAP password from the session.
    3. Plugin encrypts the password and stores it alongside the snooze record.
    4. Cron job runs `check_expired_snoozes`.
    5. If no active session, the job decrypts the password, connects to IMAP, moves the message, and deletes the database record (clearing the password).

## Out of Scope
- Supporting password changes (if a user changes their password, existing snoozes will fail until processed on their next login).
- Storing credentials for any purpose other than the snooze plugin.
