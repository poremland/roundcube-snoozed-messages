# Specification: manual_unsnooze

## Overview
This track adds the ability for users to manually "unsnooze" a message previously moved to the "Snoozed" IMAP folder. This allows for immediate restoration of messages to the Inbox before their scheduled snooze time expires.

## Functional Requirements
- **UI Integration**:
    - Add a new "Unsnooze" button to the message toolbar.
    - The "Unsnooze" button should only be **visible** when the user is viewing the "Snoozed" IMAP folder.
    - The "Snooze" button should only be **visible** when the user is viewing the "INBOX" folder.
- **Custom Snooze Picker**:
    - The date/time picker should display and accept values in the **user's local timezone**.
    - The plugin should convert these local times to **UTC** before sending/storing them in the database.
- **Unsnooze Action**:
    - Move the selected message(s) from the "Snoozed" folder back to the "Inbox".
    - Mark restored messages as **unread** to ensure user visibility.
    - Delete the corresponding record from the `snoozed_messages` database table.
- **UI Feedback**:
    - Display a standard Roundcube confirmation message (e.g., "Message unsnoozed successfully").
    - Remove the restored message(s) from the message list in the "Snoozed" folder view immediately after the move.

## Non-Functional Requirements
- **Reliability**: Ensure the database record is consistently deleted regardless of whether the IMAP move succeeds or fails (to avoid ghost redelivery).
- **Native Experience**: The "Unsnooze" button and action should follow standard Roundcube plugin conventions and UI patterns.

## Acceptance Criteria
- User can select a message in the "Snoozed" folder and click the "Unsnooze" button.
- The message is moved to the "Inbox" and appears there as **unread**.
- The message is removed from the "Snoozed" folder list.
- The database entry for the message UID is removed from the `snoozed_messages` table.
- A success notification is shown.

## Out of Scope
- Unsnoozing messages that are not in the "Snoozed" folder.
- Allowing the user to choose a destination other than the Inbox.
- Batch unsnoozing from the context menu (for this track).
