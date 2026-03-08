# Implementation Plan: Fix IMAP Connection Error in Production

## Phase 1: Test & Reproduce
- [x] Task: Write Failing Unit Test ae1608c
    - [ ] Create a new test file `tests/ImapConnectionTest.php` to simulate the production error.
    - [ ] Mock the Roundcube configuration and storage instance to verify that `process_offline_restore` fails when no SSL connection options are provided.
    - [ ] Run the tests and confirm they fail.
- [x] Task: Verify Production Error Symptoms ae1608c
    - [ ] Review the `snoozed_messages.php` code to identify the exact location where connection options are retrieved and applied.
    - [ ] Confirm that `imap_conn_options` is not being provided in the failing scenario.

## Phase 2: Implementation
- [x] Task: Implement Default SSL Options a8ab7ed
    - [ ] Modify `snoozed_messages.php` to provide safe defaults (`verify_peer: false`, `verify_peer_name: false`, `allow_self_signed: true`) when `imap_conn_options` is not configured.
    - [ ] Ensure these defaults are only applied if no explicit options are found.
- [x] Task: Verify with Tests (Green Phase) a8ab7ed
    - [ ] Run the `tests/ImapConnectionTest.php` and confirm that it now passes.
    - [ ] Run the full test suite (`tests/`) to ensure no regressions are introduced.

## Phase 3: Verification & Cleanup
- [x] Task: Final Verification a8ab7ed
    - [ ] Confirm that the reported error `Could not connect to ssl://192.168.4.113:993: Unknown reason` is addressed by the new logic.
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Verification & Cleanup' (Protocol in workflow.md)
