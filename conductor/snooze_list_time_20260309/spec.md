# Specification: Display Snooze Time in Message List

## Overview
The goal of this track is to add a "snoozed message time" to the message list in the Roundcube Snooze Plugin, specifically when viewing the "Snoozed" folder. This information will inform the user how much longer a message will remain snoozed.

## Functional Requirements
- **Display Snooze Time:** Add the relative time (e.g., "in 2 hours", "tomorrow at 9:00 AM") of a snoozed message to the message list.
- **Location:** Display the time on the same line as the subject, but under the standard message time.
- **Context:** Only show the snooze time when the user is viewing the "Snoozed" folder.
- **Formatting:** Use the label "Snoozed until: " followed by the relative time.
- **Icon:** Include a snooze icon (e.g., a clock or similar) next to the "Snoozed until" label.
- **Integration:** Hook into the Roundcube message list loading process to fetch and display the snooze time from the database.
- **Version Bump:** Increment the plugin version to 1.1.0 upon completion of this track.

## Non-Functional Requirements
- **Performance:** Ensure fetching snooze times for the message list doesn't introduce significant latency.
- **UI Consistency:** Styling must match the current Roundcube skin (Elastic).

## Acceptance Criteria
- The "Snoozed until" time is visible in the message list for all messages in the "Snoozed" folder.
- The time is formatted relatively (e.g., "in 1 hour").
- An icon is displayed next to the label.
- The time is not visible in other folders (e.g., Inbox, Sent).
- The placement is correct (under the message time, aligned with the subject line).
- The plugin version in `composer.json` is bumped to 1.1.0.

## Out of Scope
- Displaying snooze times in search results across folders.
- Changing the snooze duration from the message list.
