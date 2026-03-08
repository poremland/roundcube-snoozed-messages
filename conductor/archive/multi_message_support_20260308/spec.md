# Specification: Multi-Message Snooze and Unsnooze Support

## Overview
Enable users to snooze and unsnooze multiple messages at once. Currently, the plugin only processes the first selected message.

## Requirements
- **Multiple Selection:** The UI should send all currently selected UIDs to the backend.
- **Batch Processing:** The backend should iterate through all provided UIDs and apply the snooze or unsnooze operation to each.
- **UI Feedback:**
    - On success, all successfully processed messages should be removed from the current message list.
    - If some messages fail, the UI should still remove the successful ones and show an appropriate message.
- **Robustness:** A failure in snoozing one message in a batch should not prevent others from being snoozed.

## Implementation Details
- **JS:** Change `selected[0]` to `selected.join(',')` in `http_post` calls.
- **PHP:** 
    - Update `snooze_action` to handle comma-separated UIDs.
    - Update `unsnooze_action` to handle comma-separated UIDs.
    - Track successful UIDs and return them in the `plugin.snooze-success` command.

## Out of Scope
- Optimizing IMAP MOVE to a single batch command (will continue to use per-message logic to ensure reliable Message-ID capture and DB insertion).
