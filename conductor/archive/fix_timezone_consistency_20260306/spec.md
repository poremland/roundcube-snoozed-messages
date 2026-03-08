# Specification: fix_timezone_consistency

## Overview
Fix the discrepancy between UTC and local time in the `snoozed_messages` table and implementation logic. Ensure all storage and comparisons are performed in UTC to prevent re-delivery failures and daylight saving time issues. Implement cleanup for "ghost" records (messages manually moved out of the Snoozed folder).

## Functional Requirements
- **UTC Consistency**:
    - Update `calculate_until` to always return timestamps in UTC.
    - Update the redelivery query to use `UTC_TIMESTAMP()` instead of `NOW()`.
    - Update the database schema to ensure `created_at` defaults to UTC if possible, or handle it in application code.
- **Ghost Cleanup**:
    - During the `check_expired_snoozes` task, if a message is missing from the "Snoozed" folder, assume it was manually moved/deleted and remove the corresponding database record.
- **Robustness**:
    - Ensure the application handles cases where a message ID in the database no longer exists in the "Snoozed" folder without throwing fatal errors.
- **Local Time Display**:
    - Ensure all user-facing dates are displayed in the user's local timezone.

## Acceptance Criteria
- Emails snoozed via the UI (which sends UTC) are correctly identified as expired by the backend task.
- `snoozed_until` values in the DB are compared against UTC time.
- Records for messages manually moved out of the "Snoozed" folder are automatically purged during the next check cycle.
- The custom snooze picker displays the user's current local time by default.

## Out of Scope
- Converting the user's browser UI to a different timezone (handled in a previous track).
- Supporting non-MySQL databases (for now).
