# Roundcube Snoozed Messages Plugin

A Roundcube plugin that enables you to snooze an email for a specific period of time, helping you achieve **Inbox Zero**.

The Roundcube Snooze Plugin is designed to help users manage their workflow by temporarily removing emails from their active inbox and scheduling them to reappear at a more appropriate time.

## Features

- **Quick Snooze Options:** Provide predefined, one-click snooze durations (e.g., 1 hour, later today, tomorrow morning).
- **Custom Snooze Duration:** Include a date and time picker for precise control over when an email should reappear.
- **Manual Unsnooze:** Allow users to manually restore snoozed messages to their Inbox at any time from the "Snoozed" folder.
- **Automated Re-delivery:** The plugin performs regular background checks for emails whose snooze period has expired and moves them back to the Inbox.
- **Secure Credential Storage:** Employs "dual-compromise" encryption for any sensitive background credentials, requiring access to both the database and the filesystem to decrypt.

## Requirements

- Roundcube 1.5+ (Elastic skin recommended)
- PHP 8.0+
- MySQL/MariaDB/PostgreSQL database

## Installation

Please see [INSTALL.md](INSTALL.md) for detailed installation and configuration instructions.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See the [LICENSE](LICENSE) file for the full text.

## Credits

Developed by **Paul Oremland**.
