# Implementation Plan: Fix Snooze Label Layout in Safari

## Phase 1: Research & Structure Update
- [ ] Task: Identify reliable positioning cell in Elastic skin
    - [ ] Inspect Elastic skin DOM for message list rows
- [ ] Task: Update JS to append label to a safer target
    - [ ] Modify `snoozed_messages.js` to find the `.date` cell and ensure it has `position: relative`
- [ ] Task: Conductor - User Manual Verification 'DOM Update' (Protocol in workflow.md)

## Phase 2: CSS Refinement
- [ ] Task: Update CSS for new target
    - [ ] Refine `.snooze-until` styles to work within the `.date` cell
    - [ ] Remove unreliable `tr`/`li` positioning overrides
- [ ] Task: Conductor - User Manual Verification 'Safari UI Check' (Protocol in workflow.md)

## Phase 3: Version Bump & Finalization
- [x] Task: Update version numbers
    - [x] Update version to `1.1.1` in `composer.json`
    - [x] Update version to `1.1.1` in `snoozed_messages.php` docblock
- [ ] Task: Conductor - User Manual Verification 'Final Release Check' (Protocol in workflow.md)

## Phase: Review Fixes
- [x] Task: Apply review suggestions 3895399
