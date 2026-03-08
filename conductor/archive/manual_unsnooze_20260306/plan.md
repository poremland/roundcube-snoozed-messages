# Implementation Plan: manual_unsnooze

## Phase 1: UI Integration
- [x] Task: Add "Unsnooze" button to the toolbar (850f4ce)
    - [x] Write failing test to verify button presence in Snoozed folder context
    - [x] Implement `template_container` hook or similar to inject the button
- [x] Task: Implement button visibility logic (850f4ce)
    - [x] Write failing test to ensure button is only shown in the "Snoozed" folder
    - [x] Update plugin logic to toggle button visibility based on the current folder
- [ ] Task: Conductor - User Manual Verification 'Phase 1: UI Integration' (Protocol in workflow.md)

## Phase 2: Backend Logic [checkpoint: 8d742e8]
- [x] Task: Create the unsnooze AJAX action (95d625a)
    - [x] Write failing test for the `plugin.unsnooze-action` endpoint
    - [x] Register the new action in the plugin's `init()` method
- [x] Task: Implement IMAP move and unread state (95d625a)
    - [x] Write failing test to verify message move to Inbox and unread flag set
    - [x] Implement the move logic using Roundcube's storage API
- [x] Task: Implement database cleanup (95d625a)
    - [x] Write failing test to verify record deletion from `snoozed_messages` table
    - [x] Implement the SQL deletion in the action handler
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Backend Logic' (Protocol in workflow.md)

## Phase 3: Final Polish & Verification [checkpoint: 064f6be]
- [x] Task: Implement local timezone handling in custom picker (064f6be)
    - [x] Update JS to use local time for default value
    ## Phase 3: Final Polish & Verification [checkpoint: 9b791c5]
    - [x] Task: Add client-side success feedback (adf6a30)
        - [x] Write failing test to verify message removal from list and success notification
        - [x] Implement the JavaScript callback to handle the unsnooze success event
    - [x] Task: Final project verification (adf6a30)
        - [x] Run full test suite
        - [x] Perform end-to-end manual verification
    - [ ] Task: Conductor - User Manual Verification 'Phase 3: Final Polish & Verification' (Protocol in workflow.md)

    ## Phase: Review Fixes
    - [x] Task: Apply review suggestions (061c6f1)
