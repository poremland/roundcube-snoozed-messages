# Specification: Fix IMAP Connection Error in Production

## Overview
This track addresses a production error where the Snooze plugin fails to connect to the IMAP server via SSL, specifically for internal IP addresses (e.g., `192.168.4.113`). The error message `Could not connect to ssl://192.168.4.113:993: Unknown reason` suggests a failure in SSL/TLS certificate verification, common with self-signed certificates or IP-based hosts.

## Functional Requirements
- **Resilient SSL Connection:** Implement a mechanism to allow IMAP connections to succeed even when the server uses a self-signed certificate or is accessed via an internal IP address.
- **Default SSL Options:** Ensure that if no explicit `imap_conn_options` are provided in the Roundcube configuration, the plugin provides safe defaults (e.g., `verify_peer: false`, `verify_peer_name: false`, `allow_self_signed: true`).
- **Config Persistence:** Ensure these options are correctly passed to the `rcube_storage` / `rcube_imap` instance used during the offline redelivery process.

## Non-Functional Requirements
- **Backward Compatibility:** Maintain existing behavior for users who have already configured `imap_conn_options`.
- **Reliability:** The background/CLI redelivery process must be robust against common SSL/TLS configuration issues.

## Acceptance Criteria
- [ ] Unit tests verify that `process_offline_restore` applies the correct SSL connection options when `imap_conn_options` is missing.
- [ ] All existing tests pass, ensuring no regressions in the snooze or redelivery logic.
- [ ] The "Unknown reason" connection error is resolved for the reported production scenario.

## Out of Scope
- Implementing a full-featured UI for managing IMAP connection settings.
- Changes to the core Roundcube IMAP library itself.
