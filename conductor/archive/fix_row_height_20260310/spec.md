# Specification: Fix Message List Row Height

## Overview
The plugin recently introduced a "Snoozed until" label in the message list for the Snoozed mailbox. This required increasing the row height. However, the CSS change was applied globally, causing increased row height in all mailboxes (Inbox, Sent, etc.), which is undesirable. This track aims to scope the row height increase exclusively to the "Snoozed" mailbox.

## Functional Requirements
1. **Scope Row Height Fix**: Restrict the row height increase (introduced for the "Snoozed until" label) to the "Snoozed" mailbox only.
2. **Standard Height for Others**: Ensure other mailboxes retain their standard Roundcube Elastic skin row height.
3. **Version Bump**: Increment the patch version of the plugin (e.g., 1.1.1 -> 1.1.2).

## Non-Functional Requirements
1. **Visual Consistency**: No regressions in the "Snoozed" mailbox view; the "Snoozed until" label must still fit correctly.
2. **CSS Specificity**: CSS changes should be clean and specific to the Snoozed folder context.

## Acceptance Criteria
1. In the **Snoozed** mailbox, rows have the increased height to accommodate the "Snoozed until" label.
2. In **all other mailboxes** (Inbox, Drafts, Sent, Junk, Trash, etc.), rows have the standard Roundcube Elastic height.
3. The plugin version in `composer.json` and/or the main PHP file is updated by a patch version.

## Out of Scope
- Changing the design or content of the "Snoozed until" label itself.
- Changes to other skins (e.g., Larry) unless they are also affected (Elastic confirmed as primary target).
