# Implementation Plan: Multi-Message Snooze and Unsnooze Support

## Phase 1: Frontend Update
- [x] Task: Update `snoozed_messages.js` to send multiple UIDs (ec8aae0)
    - [x] Update `plugin.snooze-action` command to join selection
    - [x] Update `plugin.unsnooze-action` command to join selection
    - [x] Update custom snooze dialog to join selection

## Phase 2: Backend Update
- [x] Task: Update `snooze_action` in `snoozed_messages.php` (ec8aae0)
    - [x] Handle comma-separated UIDs
    - [x] Iterate and track successes
- [x] Task: Update `unsnooze_action` in `snoozed_messages.php` (ec8aae0)
    - [x] Handle comma-separated UIDs
    - [x] Iterate and track successes

## Phase 3: Verification
- [x] Task: Update automated tests (ec8aae0)
    - [x] Add test cases for multiple UIDs in `MultiMessageTest.php`
- [x] Task: Manual Verification (ec8aae0)
    - [x] Select multiple messages and snooze
    - [x] Select multiple messages and unsnooze
