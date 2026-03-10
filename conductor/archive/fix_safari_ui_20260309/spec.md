# Specification: Fix Snooze Label Layout in Safari

## Overview
The "Snoozed until" label currently bunches at the bottom of the screen in Safari because `position: relative` on `tr` elements is unreliable in that browser. This track will move the label into a specific cell and use a more robust positioning strategy.

## Functional Requirements
- **Safari Compatibility:** Ensure the "Snoozed until" label appears correctly under the date in every message row in Safari.
- **Robust Positioning:** Change the DOM structure or CSS to ensure labels are contained within their respective rows.
- **Visual Consistency:** Maintain the existing look and feel: right-aligned, small text, clock icon.
- **Version Bump:** Increment the plugin version to 1.1.1 upon completion.

## Acceptance Criteria
- Labels are correctly positioned under the date in Safari.
- No labels are "bunched" at the bottom of the list.
- Cross-browser compatibility (Chrome, Firefox, Safari) is maintained.
- Plugin version is bumped to 1.1.1 in `composer.json` and `snoozed_messages.php`.
