# Track: Offline Redelivery with Encrypted Credential Vault

## Status: [~] In Progress

## Documentation
- [Specification](./spec.md)
- [Implementation Plan](./plan.md)

## Description
This track enables the plugin to unsnooze messages even when the user is not actively logged in. It achieves this by securely storing encrypted IMAP credentials in the database, which the background cron job can use to perform necessary message moves.
