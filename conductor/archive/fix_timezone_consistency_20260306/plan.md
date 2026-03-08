# Implementation Plan: fix_timezone_consistency

## Phase 1: UTC Normalization
- [x] Task: Update timestamp generation logic (e7080e3)
    - [x] Update `calculate_until` in `snoozed_messages.php` to explicitly use UTC for the base `DateTime` object
    - [x] Update the manual timestamp parsing logic to ensure it treats incoming strings as UTC
- [x] Task: Update redelivery query (e7080e3)
    - [x] Update `check_expired_snoozes` to use `UTC_TIMESTAMP()` in the SQL query
- [x] Task: Verify UTC consistency (e7080e3)
    - [x] Write a test to snooze a message and verify the stored value matches the expected UTC time
    - [x] Write a test to ensure `check_expired_snoozes` correctly picks up UTC-based expired records
- [ ] Task: Conductor - User Manual Verification 'Phase 1: UTC Normalization' (Protocol in workflow.md)

## Phase 2: Ghost Cleanup & Robustness [checkpoint: 3f98fd3]
- [x] Task: Implement message existence check (3f98fd3)
    - [x] Update `check_expired_snoozes` to handle `restore_message` returning false (e.g., message not found)
    - [x] Implement logic to delete the database record even if the IMAP move fails due to missing message
- [x] Task: Verify ghost cleanup (3f98fd3)
    - [x] Write a test to simulate a database record for a message that doesn't exist in the IMAP folder
    - [x] Verify the record is purged by the task without error
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Ghost Cleanup & Robustness' (Protocol in workflow.md)

## Phase 3: Local Time UI Verification
- [ ] Task: Verify and ensure local time display in UI
    - [ ] Audit `snoozed_messages.js` to ensure the custom picker default and display logic correctly use the browser's local time
    - [ ] Verify that any other potential date displays (tooltips, etc.) convert UTC storage values back to local time before rendering
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Local Time UI Verification' (Protocol in workflow.md)

## Phase: Review Fixes
- [x] Task: Apply review suggestions (601d542)
    - [x] Implement robust Message-ID header tracking for cross-folder consistency
    - [x] Finalize database cleanup logic for manual unsnooze
    - [x] Remove debug logging and verify production readiness
