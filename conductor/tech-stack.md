# Tech Stack: Roundcube Snooze Plugin

## Language
- **PHP (8.x+):** The primary server-side language for Roundcube plugins.

## Backend Framework & APIs
- **Roundcube Plugin API:** Utilizes the official hooks and functions for seamless integration.
- **Roundcube Database Abstraction Layer (DB):** For persistent storage of snooze times in the Roundcube database.
- **Roundcube IMAP API:** For moving emails between the inbox and the "Snoozed" folder.

## Frontend Technologies
- **JavaScript (Vanilla/jQuery):** For client-side logic, handling UI interactions, and making AJAX requests to the backend.
- **CSS:** For styling new UI elements to match Roundcube skins.

## Persistence
- **MySQL/MariaDB/PostgreSQL:** The underlying database managed by Roundcube.

## Key Features & Tools
- **PHP Cron/Task Scheduler:** For periodic background checks to identify and restore snoozed emails.
- **Roundcube Hooks:** For intercepting events like message list loading or message deletion to provide a seamless snooze experience.
- **Roundcube Skins Support:** Compatibility with standard Roundcube skins (e.g., Elastic, Larry).
- **GitHub Actions:** For automated testing and continuous integration.
