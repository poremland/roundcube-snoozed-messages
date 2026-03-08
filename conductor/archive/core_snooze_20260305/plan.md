# Implementation Plan: Core Snooze Functionality (v1.0.0)

## Phase 1: Foundation [checkpoint: 4ed0f3d]
- [x] Task: Create database migration for `snoozed_messages` table (365dac3)
    - [x] Write migration script (PHP)
    - [x] Implement `db_setup` hook in the plugin
- [x] Task: Initialize basic plugin structure (00a93e8)
    - [x] Create `snoozed_messages.php` with basic class and metadata
    - [x] Implement `init()` method
- [x] Task: Conductor - User Manual Verification 'Phase 1: Foundation' (Protocol in workflow.md)

## Phase 2: UI Integration [checkpoint: 07dbb4f]
- [x] Task: Add "Snooze" button to the message toolbar (1a185c4)
    - [x] Register `render_page` hook to inject CSS/JS
    - [x] Implement `template_container` hook to add the button
- [x] Task: Create the Snooze dropdown menu (2f97ecf)
    - [x] Implement JavaScript to handle button click and show menu
    - [x] Define quick-snooze options in the menu
- [x] Task: Implement the Custom Snooze picker (24b4e2d)
    - [x] Integrate a standard date/time picker (e.g., Roundcube's internal or a lightweight library)
    - [x] Create a modal dialog for custom input
- [x] Task: Conductor - User Manual Verification 'Phase 2: UI Integration' (Protocol in workflow.md)

## Phase 3: Core Logic [checkpoint: 0b0643e]
- [x] Task: Implement the snooze action (backend) (99e62a5)
    - [x] Create an AJAX action to handle snooze requests
    - [x] Implement logic to move the email to the "Snoozed" IMAP folder
    - [x] Save the snooze metadata to the database
- [x] Task: Handle visual feedback for snoozed emails (1238e26)
    - [x] Update the UI after a successful snooze (e.g., remove message from list)
- [x] Task: Conductor - User Manual Verification 'Phase 3: Core Logic' (Protocol in workflow.md)

## Phase 4: Automated Re-delivery [checkpoint: a050e01]
- [x] Task: Implement the background check for expired snoozes (83441eb)
    - [x] Create a periodic task (e.g., via Roundcube's `task` hook or a standalone script)
    - [x] Query the database for expired snooze times
- [x] Task: Restore emails to the inbox (83441eb)
    - [x] Implement logic to move emails from "Snoozed" back to "Inbox"
    - [x] Mark restored emails as **unread**
- [x] Task: Conductor - User Manual Verification 'Phase 4: Automated Re-delivery' (Protocol in workflow.md)

## Phase 5: Final Polish & Verification [checkpoint: a5ba7e3]
- [x] Task: Refine UI/UX and styling (0eb8930)
    - [x] Ensure visual consistency with Roundcube skins (Elastic/Larry)
    - [x] Handle edge cases (e.g., "Snoozed" folder missing, IMAP connection issues)
- [x] Task: Final project verification (040ec46)
    - [x] Run full test suite
    - [x] Perform end-to-end manual verification
- [x] Task: Conductor - User Manual Verification 'Phase 5: Final Polish & Verification' (Protocol in workflow.md)

## Phase: Review Fixes
- [x] Task: Apply review suggestions (c0e35ea)
