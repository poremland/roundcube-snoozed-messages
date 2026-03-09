# Initial Concept

a roundcube plugin (doc: https://github.com/roundcube/roundcubemail/wiki/Plugin-API) that enables you to snooze an email for a specific period of time.

---

# Product Definition: Roundcube Snooze Plugin

## Vision
The Roundcube Snooze Plugin is designed to help users achieve **Inbox Zero** by temporarily removing emails from their active inbox and scheduling them to reappear at a more appropriate time. It empowers productivity-focused individuals to manage their workflow without the distraction of currently irrelevant messages.

## Target Audience
- **Individual Users:** People looking for a simple way to organize their personal inbox.
- **Productivity Enthusiasts:** Users who actively use techniques like "Inbox Zero" to stay focused and organized.

## Core Goals
- **Minimize Inbox Clutter:** Provide a seamless way to clear the inbox of emails that don't require immediate action.
- **Enhanced Focus:** Allow users to "pause" certain conversations to concentrate on their current priorities.
- **Reliable Re-delivery:** Ensure snoozed emails reappear in the inbox at the exact time requested by the user.

## Functional Requirements
- **Quick Snooze Options:** Provide predefined, one-click snooze durations (e.g., 1 hour, later today, tomorrow morning).
- **Custom Snooze Duration:** Include a date and time picker for precise control over when an email should reappear.
- **Manual Unsnooze:** Allow users to manually restore snoozed messages to their Inbox at any time from the "Snoozed" folder.
- **UI Integration:**
    - Add a dedicated **Snooze** button to the main message toolbar for easy access.
    - Add an **Unsnooze** button to the toolbar, active only when viewing the "Snoozed" folder.
    - Integrate with the Roundcube message list and preview pane to provide clear visual feedback for snoozed items, including displaying the "Snoozed until" time directly in the message list.
- **Snooze Storage & Tracking:**
    - **IMAP Integration:** When an email is snoozed, it is moved to a dedicated "Snoozed" IMAP folder.
    - **Database Persistence:** Snooze times are stored and tracked within the existing Roundcube database.
- **Automated Re-delivery:**
    - **Periodic Checks:** The plugin performs regular background checks for emails whose snooze period has expired.
    - **Restoration:** When the snooze time is up, the email is moved back to the "Inbox" folder and marked as **unread** to ensure the user notices it.

## Non-Functional Requirements
- **Plugin API Compliance:** Strictly adhere to the official Roundcube Plugin API for compatibility and stability.
- **API-Only Implementation:** Rely exclusively on Roundcube APIs and standard IMAP folder structures to ensure portability and ease of installation.
- **Performance:** Ensure that snoozing and reappearing actions are fast and don't negatively impact the webmail's responsiveness.
- **Reliability:** Background tasks for re-delivering emails must be robust and handle edge cases like server restarts or session timeouts.

## Security & Privacy
- **Framework-Native Protections:** Leverage Roundcube's built-in mechanisms for authentication, CSRF protection, and database abstraction.
- **Data Isolation:** Ensure strict per-user isolation for all database and IMAP operations.
- **Credential Safety:** Employ "dual-compromise" encryption for any sensitive background credentials, requiring access to both the database and the filesystem to decrypt.
- **Regular Audits:** Maintain a high security posture through periodic security reviews and adherence to OWASP Top 10 best practices.
- **Open Source:** Licensed under the GNU Affero General Public License version 3.0 (AGPL v3) to ensure community collaboration and transparency.
