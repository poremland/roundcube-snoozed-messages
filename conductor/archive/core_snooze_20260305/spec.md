# Specification: Core Snooze Functionality (v1.0.0)

## Background
Roundcube users need a way to manage their inbox by temporarily removing emails and scheduling them to reappear at a more appropriate time. This "snooze" functionality is a key component of the "Inbox Zero" philosophy.

## Goals
- Enable users to snooze emails using predefined durations (e.g., 1 hour, later today, tomorrow morning).
- Allow users to select a custom snooze date and time.
- Integrate seamlessly into the Roundcube UI via a dedicated toolbar button.
- Use IMAP folders and a local database to track snoozed emails.
- Automatically move emails back to the inbox and mark them as unread when the snooze period ends.

## Out of Scope
- Snoozing multiple emails simultaneously (v1.1.0).
- Advanced snooze rules (e.g., "always snooze from this sender").
- Integration with external calendar services.

## Technical Design

### Database Schema
- Table: `snoozed_messages`
- Columns:
    - `id`: Primary Key
    - `user_id`: Foreign Key to Roundcube users
    - `message_id`: IMAP message ID
    - `folder`: Original IMAP folder
    - `snoozed_until`: Timestamp for re-delivery
    - `created_at`: Creation timestamp

### IMAP Integration
- A dedicated "Snoozed" folder will be created (if it doesn't exist) to store snoozed emails.

### UI Components
- **Toolbar Button:** A "Snooze" button in the message toolbar.
- **Snooze Menu:** A dropdown menu with quick options and a "Custom..." option.
- **Custom Picker:** A modal dialog with a date and time picker.

## Security
- Ensure that snooze actions are only performed for the authenticated user.
- Validate all inputs for the custom snooze time.
- Sanitize all message IDs and folder names used in IMAP operations.
