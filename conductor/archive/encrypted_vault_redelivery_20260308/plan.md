# Implementation Plan: Offline Redelivery with Encrypted Credential Vault

## Phase 1: Security Infrastructure & Schema
- [x] Task: Update database schema (30fc93b)
    - [x] Update `SQL/mysql.initial.sql` to include `encrypted_password` TEXT column
    - [x] Update `src/Migration.php` to handle the new column
- [x] Task: Implement Encryption Helper (30fc93b)
    - [x] Create/Verify utility methods for encryption/decryption using Roundcube's core library

## Phase 2: Credential Capture
- [x] Task: Capture password during active session (30fc93b)
    - [x] Update `snooze_message` in `snoozed_messages.php` to retrieve the current user's password from the session
    - [x] Encrypt the password and include it in the `INSERT` statement

## Phase 3: Offline Redelivery Logic
- [x] Task: Update `check_expired_snoozes` for global context (30fc93b)
    - [x] Modify the logic to allow processing without a pre-existing `rcmail->user` context
    - [x] Implement per-record IMAP connection:
        - [x] Decrypt stored password
        - [x] Authenticate with IMAP server
        - [x] Perform restoration move
        - [x] Close connection
    - [x] Ensure robust error handling to prevent one failure from stopping the entire job

## Phase 4: Verification & Cleanup
- [x] Task: Write automated tests (30fc93b)
    - [x] Test encryption/decryption logic
    - [x] Mock CLI cron execution and verify IMAP authentication is triggered
- [x] Task: Manual Verification (e674cb8)
    - [x] Snooze an email, log out, and verify it returns to Inbox via cron
