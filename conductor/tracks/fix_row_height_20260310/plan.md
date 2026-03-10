# Implementation Plan: Fix Message List Row Height

This plan addresses the issue where the increased row height for snoozed messages was accidentally applied to all mailboxes. We will scope the CSS change to only apply when viewing the Snoozed folder.

## Phase 1: Research & Preparation
- [x] Task: Verify the CSS selectors currently causing the global row height increase. ea9de33
- [x] Task: Confirm how to detect the current folder in both PHP and JS. ea9de33

## Phase 2: Implementation (TDD)

### Step 1: Version Bump
- [x] Task: Bump version to 1.1.2 in `snoozed_messages.php` and `composer.json`. 14b21cc

### Step 2: Toggle Folder Class in JavaScript
- [ ] Task: Update `snoozed_messages.js` to add the `snooze-folder` class to `<body>` when the current mailbox is the Snoozed folder, and remove it otherwise.

### Step 3: Scope CSS to Snooze Folder
- [ ] Task: Update `skins/elastic/snoozed_messages.css` to prefix row height rules with `.snooze-folder`.

## Phase 3: Verification & Cleanup
- [ ] Task: Manual Verification: Ensure Snoozed folder has increased height and Inbox has standard height.
- [ ] Task: Conductor - User Manual Verification 'Fix Message List Row Height' (Protocol in workflow.md)
