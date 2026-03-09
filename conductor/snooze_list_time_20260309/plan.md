# Implementation Plan: Display Snooze Time in Message List

## Phase 1: Backend - Data Retrieval & Hook Integration
- [ ] Task: Implement `messages_list` hook in `snoozed_messages.php`
    - [ ] Register `messages_list` hook in `init()`
    - [ ] Implement hook callback `messages_list_handler`
    - [ ] Inside the handler, verify the current mailbox is the "Snoozed" folder
    - [ ] Collect all message UIDs from the `messages` argument
    - [ ] **SQL Query:** Fetch `message_id` and `snoozed_until` from `snoozed_messages` where `user_id = ?` AND `message_id IN (...)`
    - [ ] Iterate through the results and add a `snooze_until` property to the corresponding header objects in the `messages` array
- [ ] Task: Conductor - User Manual Verification 'Backend Integration' (Protocol in workflow.md)

## Phase 2: Frontend - UI Implementation & Styling
- [ ] Task: Add CSS styling for snooze time
    - [ ] Update `skins/elastic/snoozed_messages.css` with styles for the snooze time label and icon
- [ ] Task: Update JS to render snooze time
    - [ ] Modify `snoozed_messages.js` to hook into Roundcube's list update event
    - [ ] Implement logic to find message list rows and inject the "Snoozed until" label
    - [ ] Implement relative time formatting helper in JS
- [ ] Task: Conductor - User Manual Verification 'Frontend Implementation' (Protocol in workflow.md)

## Phase 3: Version Bump & Finalization
- [ ] Task: Update version numbers
    - [ ] Update version to `1.1.0` in `composer.json`
    - [ ] Update version to `1.1.0` in `snoozed_messages.php` docblock
- [ ] Task: Conductor - User Manual Verification 'Finalization' (Protocol in workflow.md)
