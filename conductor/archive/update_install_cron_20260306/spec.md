# Specification: update_install_cron

## Overview
Improve the installation instructions for setting up the automated re-delivery task scheduler (Step 5 in `INSTALL.md`). The current instructions are vague; they need to be replaced with clear, step-by-step procedures for different environments to ensure users can reliably configure the background tasks required for email re-delivery.

## Functional Requirements
- **Linux Crontab Instructions**:
    - Provide a step-by-step guide for adding a cron job to the system crontab.
    - Include a clear example command that triggers Roundcube's internal task hooks.
- **Web Cron / Shared Hosting Instructions**:
    - Provide instructions for users who cannot access the system crontab.
    - Suggest methods like using a hosting provider's control panel or a web-based cron service to ping a Roundcube URL.
- **Generic Pathing**:
    - Use clear placeholders like `/path/to/roundcube` and `/path/to/php`.
- **Verification Guide**:
    - Add a "How to Verify" sub-section under Step 5.
    - Provide instructions on how to confirm the scheduler is successfully triggering the snooze check (e.g., checking for log entries or database updates).

## Non-Functional Requirements
- **Clarity**: Instructions must be unambiguous and easy to follow for users with varying technical expertise.
- **Consistency**: Maintain the existing style, tone, and formatting of `INSTALL.md`.

## Acceptance Criteria
- `INSTALL.md` Step 5 is updated with detailed Linux Crontab instructions.
- `INSTALL.md` Step 5 is updated with instructions for Web Cron/Shared Hosting.
- `INSTALL.md` Step 5 includes a new "How to Verify" section.
- All instructions use standard placeholders for paths.

## Out of Scope
- Changing the underlying re-delivery logic or code.
- Modifying other parts of the installation guide unrelated to Step 5.
