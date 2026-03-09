# Implementation Plan: Display Snooze Time in Message List

## Phase 1: Backend - Data Retrieval & Hook Integration [checkpoint: fdcd009]
- [x] Task: Implement `messages_list` hook in `snoozed_messages.php` fdcd009
    - [x] Register `messages_list` hook in `init()`
    - [x] Implement hook callback `messages_list_handler`
    - [x] Inside the handler, verify the current mailbox is the "Snoozed" folder
    - [x] Collect all message UIDs from the `messages` argument
    - [x] **SQL Query:** Fetch `message_id` and `snoozed_until` from `snoozed_messages` where `user_id = ?` AND `message_id IN (...)`
    - [x] Iterate through the results and add a `snooze_until` property to the corresponding header objects in the `messages` array
- [x] Task: Conductor - User Manual Verification 'Backend Integration' (Protocol in workflow.md)

## Phase 2: Frontend - UI Implementation & Styling [checkpoint: 3773517]
- [x] Task: Add CSS styling for snooze time 3773517
    - [x] Update `skins/elastic/snoozed_messages.css` with styles for the snooze time label and icon
- [x] Task: Update JS to render snooze time 3773517
    - [x] Modify `snoozed_messages.js` to hook into Roundcube's list update event
    - [x] Implement logic to find message list rows and inject the "Snoozed until" label
    - [x] Implement relative time formatting helper in JS
- [x] Task: Conductor - User Manual Verification 'Frontend Implementation' (Protocol in workflow.md)

## Phase 3: Version Bump & Finalization [checkpoint: d79bf29]
- [x] Task: Update version numbers d79bf29
    - [x] Update version to `1.1.0` in `composer.json`
    - [x] Update version to `1.1.0` in `snoozed_messages.php` docblock
- [x] Task: Conductor - User Manual Verification 'Finalization' (Protocol in workflow.md)

## Phase: Review Fixes
- [x] Task: Apply review suggestions 09cb110
