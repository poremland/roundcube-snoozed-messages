# Implementation Plan: Fix IMAP Connection Error in Production

## Phase 1: Test & Reproduce
- [ ] Task: Write Failing Unit Test
    - [ ] Create a new test file `tests/ImapConnectionTest.php` to simulate the production error.
    - [ ] Mock the Roundcube configuration and storage instance to verify that `process_offline_restore` fails when no SSL connection options are provided.
    - [ ] Run the tests and confirm they fail.
- [ ] Task: Verify Production Error Symptoms
    - [ ] Review the `snoozed_messages.php` code to identify the exact location where connection options are retrieved and applied.
    - [ ] Confirm that `imap_conn_options` is not being provided in the failing scenario.

## Phase 2: Implementation
- [ ] Task: Implement Default SSL Options
    - [ ] Modify `snoozed_messages.php` to provide safe defaults (`verify_peer: false`, `verify_peer_name: false`, `allow_self_signed: true`) when `imap_conn_options` is not configured.
    - [ ] Ensure these defaults are only applied if no explicit options are found.
- [ ] Task: Verify with Tests (Green Phase)
    - [ ] Run the `tests/ImapConnectionTest.php` and confirm that it now passes.
    - [ ] Run the full test suite (`tests/`) to ensure no regressions are introduced.

## Phase 3: Verification & Cleanup
- [ ] Task: Final Verification
    - [ ] Confirm that the reported error `Could not connect to ssl://192.168.4.113:993: Unknown reason` is addressed by the new logic.
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Verification & Cleanup' (Protocol in workflow.md)
