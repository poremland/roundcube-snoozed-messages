# Installation Guide: Roundcube Snooze Plugin

This guide provides instructions for manually installing the Roundcube Snooze plugin in your Roundcube environment.

## Prerequisites

- Roundcube Webmail version 1.5.0 or higher.
- PHP version 8.0 or higher.
- Access to the Roundcube server's file system and database.
- Composer installed on the server.

## Installation Steps

1.  **Download the Plugin:**
    Download the plugin's source code and place it in the `plugins/` directory of your Roundcube installation. Ensure the folder is named `snoozed_messages`.

    ```bash
    cd /path/to/roundcube/plugins
    # Copy or clone the plugin here
    ```

2.  **Install Dependencies:**
    Navigate to the `snoozed_messages` directory and install the necessary PHP dependencies using Composer.

    ```bash
    cd snoozed_messages
    composer install --no-dev
    ```

3.  **Enable the Plugin:**
    Open the Roundcube configuration file (`config/config.inc.php`) and add `snoozed_messages` to the `$config['plugins']` array.

    ```php
    $config['plugins'] = [
        // ... other plugins
        'snoozed_messages',
    ];
    ```

4.  **Database Setup:**
    The plugin attempts to automatically create its necessary database table (`snoozed_messages`) on first run. However, if your database user lacks `CREATE` permissions, you must manually import the initial schema:

    ```bash
    mysql -u roundcube_user -p roundcube_db < plugins/snoozed_messages/SQL/mysql.initial.sql
    ```

5.  **Configure Re-delivery (Cron Job):**
    To enable automated "offline" re-delivery of snoozed emails, you must set up a periodic cron job to trigger Roundcube's CLI environment.

    Add the following entry to your system's crontab (e.g., `crontab -e`):

    ```cron
    # Run the snooze check every 5 minutes (trigger via Roundcube cleandb task)
    */5 * * * * /usr/bin/php /path/to/roundcube/bin/cleandb.sh > /dev/null 2>&1
    ```

    *Note: When `cleandb.sh` is run via PHP, it initializes the Roundcube environment, which in turn triggers the plugin's background check logic.*

6.  **Verify Installation:**
    Log in to Roundcube. Select an email and confirm that a "Snooze" button appears in the toolbar.
    
    To verify re-delivery:
    1. Snooze a test email for a short period (e.g., 5 minutes).
    2. Ensure the cron job is active or run the command manually:
       ```bash
       php /path/to/roundcube/bin/cleandb.sh
       ```
    3. Confirm the email returns to your Inbox as unread once the time has passed.

## Troubleshooting

- **Snooze button not appearing:** Ensure the plugin is correctly enabled in `config/config.inc.php`.
- **Emails not reappearing:** Verify that the Roundcube periodic tasks are running and that the `snoozed_messages` table exists in your database.
- **Permission Errors:** Ensure the web server and cron user have necessary read/write permissions to the Roundcube logs and plugins directory.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See the [LICENSE](LICENSE) file for the full text.

## Credits

Developed by **Paul Oremland**.
