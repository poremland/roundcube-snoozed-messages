<?php

/**
 * Roundcube Snoozed Messages Plugin
 *
 * Copyright (C) 2026, Paul Oremland
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @version 1.1.1
 * @author Paul Oremland
 * @license AGPL-3.0-or-later
 */

// Load composer dependencies
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class snoozed_messages extends rcube_plugin
{
    private $snooze_folder = 'Snoozed';
    private static $cli_processed = false;

    /**
     * Plugin initialization.
     */
    public function init()
    {
        if (PHP_SAPI === 'cli' && !self::$cli_processed) {
            self::$cli_processed = true;
            $this->check_expired_snoozes([]);
        }

        $rcmail = $this->get_rcmail();

        if ($rcmail->output->type === 'html') {
            $this->add_texts('localization/', true);

            // Register the menu as a Roundcube GUI object
            $rcmail->output->add_gui_object('snooze-menu', 'snooze-menu');

            // Add the buttons to the toolbar (always, visibility handled by JS/CSS)
            $this->add_button([
                'id'         => 'rcmbtn_snooze',
                'command'    => 'plugin.snooze-menu',
                'type'       => 'link',
                'class'      => 'snooze disabled hidden',
                'classact'   => 'snooze',
                'label'      => 'snoozed_messages.snooze',
                'title'      => 'snoozed_messages.snoozetitle',
                'data-popup' => 'snooze-menu',
                'innerclass' => 'inner',
            ], 'toolbar');

            $this->add_button([
                'id'         => 'rcmbtn_unsnooze',
                'command'    => 'plugin.unsnooze-action',
                'type'       => 'link',
                'class'      => 'unsnooze disabled hidden',
                'classact'   => 'unsnooze',
                'label'      => 'snoozed_messages.unsnooze',
                'title'      => 'snoozed_messages.unsnoozetitle',
                'innerclass' => 'inner',
            ], 'toolbar');

            // Also add to the more menu
            $this->add_button([
                'command'    => 'plugin.snooze-menu',
                'type'       => 'link-menuitem',
                'class'      => 'snooze disabled hidden',
                'classact'   => 'snooze',
                'label'      => 'snoozed_messages.snooze',
                'data-popup' => 'snooze-menu',
                'innerclass' => 'inner',
            ], 'messagemenu');

            $this->add_button([
                'command'    => 'plugin.unsnooze-action',
                'type'       => 'link-menuitem',
                'class'      => 'unsnooze disabled hidden',
                'classact'   => 'unsnooze',
                'label'      => 'snoozed_messages.unsnooze',
                'innerclass' => 'inner',
            ], 'messagemenu');

            // Hook to include assets
            $this->add_hook('render_page', [$this, 'include_assets']);
        }

        $this->register_action('plugin.snooze-action', [$this, 'snooze_action']);
        $this->register_action('plugin.unsnooze-action', [$this, 'unsnooze_action']);
        $this->add_hook('task', [$this, 'check_expired_snoozes']);
        $this->add_hook('refresh', [$this, 'check_expired_snoozes']);
        $this->add_hook('messages_list', [$this, 'messages_list_handler']);
    }

    /**
     * Hook handler to inject snooze time into message list.
     *
     * @param array $args Hook arguments.
     * @return array Updated hook arguments.
     */
    public function messages_list_handler($args)
    {
        $rcmail = $this->get_rcmail();
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_GPC) ?: $rcmail->storage->get_folder();

        if ($mbox !== $this->snooze_folder) {
            $rcmail->output->set_env('snooze_data', null);
            return $args;
        }

        if (empty($args['messages'])) {
            return $args;
        }

        $db = $rcmail->get_dbh();
        $user_id = isset($rcmail->user->ID) ? $rcmail->user->ID : 1;
        $table_name = $db->table_name('snoozed_messages');

        $sql = "SELECT user_id, message_id, message_id_header, snoozed_until FROM $table_name WHERE user_id = ?";
        $result = $db->query($sql, $user_id);

        $db_records = [];
        while ($row = $db->fetch_assoc($result)) {
            $db_records[] = $row;
        }

        if (empty($db_records)) {
            $rcmail->output->set_env('snooze_data', null);
            return $args;
        }

        $snooze_data = [];
        foreach ($args['messages'] as $msg) {
            $uid = (string)$msg->uid;
            $message_id_header = $this->get_message_id_header($uid, $this->snooze_folder);

            foreach ($db_records as $row) {
                $matched = false;

                if ($message_id_header && $row['message_id_header'] === $message_id_header) {
                    $matched = true;
                }
                else if ($row['message_id'] === $uid) {
                    $matched = true;
                }

                if ($matched) {
                    $snooze_data[$uid] = $row['snoozed_until'];
                    break;
                }
            }
        }

        if (empty($snooze_data)) {
            $rcmail->output->set_env('snooze_data', null);
            return $args;
        }

        $rcmail->output->set_env('snooze_data', $snooze_data);

        return $args;
    }

    /**
     * Setup plugin environment (tables and folders).
     */
    private function setup_plugin_environment()
    {
        $rcmail = $this->get_rcmail();
        $db = $rcmail->get_dbh();
        if ($db) {
            $migration = new \Roundcube\SnoozedMessages\Migration($db);
            $migration->createTable();
        }

        if ($rcmail->storage && $rcmail->storage->is_connected()) {
            if (!$rcmail->storage->folder_exists($this->snooze_folder)) {
                $rcmail->storage->create_folder($this->snooze_folder, true);
            } else {
                $rcmail->storage->subscribe($this->snooze_folder);
            }
        }
    }

    /**
     * Check for expired snoozes and restore them.
     *
     * @param array $args Hook arguments.
     * @return array Updated hook arguments.
     */
    public function check_expired_snoozes($args)
    {
        $is_cli = (PHP_SAPI === 'cli');
        $rcmail = $this->get_rcmail();
        $db = $rcmail->get_dbh();
        $table_name = $db->table_name('snoozed_messages');
        $users_table = $db->table_name('users');

        $current_user_id = (!empty($rcmail->user) && !empty($rcmail->user->ID)) ? $rcmail->user->ID : null;

        // If not CLI and no user is logged in, we can't do anything (web guest)
        if (!$is_cli && !$current_user_id) {
            return $args;
        }

        // 1. Build Query: 
        // - In CLI mode: Get all expired snoozes for all users.
        // - In Web mode: Only get expired snoozes for the current authenticated user.
        $sql = "SELECT s.*, u.username, u.mail_host 
                FROM $table_name s 
                JOIN $users_table u ON s.user_id = u.user_id 
                WHERE s.snoozed_until <= UTC_TIMESTAMP()";
        
        $params = array();
        if (!$is_cli) {
            $sql .= " AND s.user_id = ?";
            $params[] = $current_user_id;
        }

        $result = $db->query($sql, $params);

        while ($row = $db->fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $restore_success = false;

            // 2. Handle Restoration:
            // A. If this is the current logged-in user, use their existing connection.
            if ($current_user_id && $user_id == $current_user_id && $rcmail->storage && $rcmail->storage->is_connected()) {
                $restore_success = $this->process_single_restore($row);
            } 
            // B. Otherwise, we need to connect manually (CLI mode or background task).
            else if (!empty($row['encrypted_password'])) {
                $restore_success = $this->process_offline_restore($row);
            }

            // 3. Cleanup: Always delete record if move succeeded or if it's a "ghost" (no password to retry)
            if ($restore_success || empty($row['encrypted_password'])) {
                $db->query("DELETE FROM $table_name WHERE id = ?", array($row['id']));
            }
        }

        return $args;
    }

    /**
     * Helper to process restoration using the current active session.
     *
     * @param array $row Database row for the snoozed message.
     * @return bool True on success, false otherwise.
     */
    private function process_single_restore($row)
    {
        $rcmail = $this->get_rcmail();
        // Finding the current UID in the Snoozed folder by header
        $search_result = $rcmail->storage->search($this->snooze_folder, 'HEADER Message-ID "' . $this->_escape_imap_string($row['message_id_header']) . '"');
        if ($search_result && $search_result->count() > 0) {
            $uids = $search_result->get();
            return $this->restore_message($uids[0], $row['folder']);
        }
        
        return false; // Could be a ghost message
    }

    /**
     * Helper to process restoration by manually connecting to IMAP.
     *
     * @param array $row Database row for the snoozed message.
     * @return bool True on success, false otherwise.
     */
    private function process_offline_restore($row)
    {
        $rcmail = $this->get_rcmail();
        $pass = $this->decrypt_password($row['encrypted_password']);
        
        if (!$pass) {
            rcube::write_log('errors', "SNOOZE: Failed to decrypt password for user " . $row['username']);
            return false;
        }

        $user = $row['username'];
        $host_str = $row['mail_host'];
        $config_host = $rcmail->config->get('imap_host');

        // If the DB host is just a hostname and the config host matches it (but with protocol/port)
        // use the config host instead as it's more complete.
        if (strpos($host_str, '://') === false && !empty($config_host)) {
            if (strpos($config_host, $host_str) !== false) {
                $host_str = $config_host;
            }
        }

        // Parse host string to extract protocol and port (Roundcube style)
        $a_host = parse_url($host_str);
        if (!empty($a_host['host'])) {
            $host = $a_host['host'];
            $use_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], ['ssl','imaps','tls'])) ? $a_host['scheme'] : null;
            $port = isset($a_host['port']) ? $a_host['port'] : ($use_ssl ? 993 : 143);
        } else {
            $host = $host_str;
            $port = 143;
            $use_ssl = null;
        }

        // Use the standard storage instance
        $storage = $this->get_storage_instance();
        
        // Load connection options (important for SSL verification etc.)
        $conn_options = $rcmail->config->get('imap_conn_options');
        
        // If no options are set, provide safe defaults for local/internal connections
        // that often use self-signed certificates or IP addresses.
        if (empty($conn_options)) {
            $conn_options = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        if (!empty($conn_options)) {
            $storage->set_options($conn_options);
        }

        // Connect using the parsed components
        if ($storage->connect($host, $user, $pass, $port, $use_ssl)) {
            // In CLI/Manual mode, we must ensure the storage object is the one used by process_single_restore
            $orig_storage = $rcmail->storage;
            $rcmail->storage = $storage;
            
            $success = $this->process_single_restore($row);
            
            $storage->close();
            $rcmail->storage = $orig_storage;
            
            return $success;
        }

        $error_str = method_exists($storage, 'get_error_str') ? $storage->get_error_str() : 'unknown error';
        rcube::write_log('errors', "SNOOZE: IMAP connect FAILED for $user at $host. Error: $error_str");
        return false;
    }

    /**
     * Factory method for storage to allow testing.
     *
     * @return rcube_storage Storage instance.
     */
    protected function get_storage_instance()
    {
        if (class_exists('rcube_imap')) {
            return new rcube_imap();
        }
        $rcmail = $this->get_rcmail();
        return $rcmail->get_storage();
    }

    /**
     * Restore a message from the snooze folder to the target folder.
     *
     * @param string $uid Message UID.
     * @param string $target_folder Target folder name.
     * @return bool True on success, false otherwise.
     */
    public function restore_message($uid, $target_folder)
    {
        $rcmail = $this->get_rcmail();
        
        // 1. Mark as unread in "Snoozed" folder BEFORE move
        $rcmail->storage->set_flag($uid, 'UNSEEN', $this->snooze_folder);
        
        // 2. Move from "Snoozed" folder back to target folder
        if ($rcmail->storage->move_message($uid, $target_folder, $this->snooze_folder)) {
            return true;
        }
        return false;
    }

    /**
     * Snooze action handler.
     */
    public function snooze_action()
    {
        $rcmail = $this->get_rcmail();
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_GPC);
        $duration = rcube_utils::get_input_value('_duration', rcube_utils::INPUT_GPC);
        $until = $this->calculate_until($duration);

        // Use Roundcube's standard way to get multiple UIDs
        $uids_per_folder = rcmail::get_uids(null, $mbox, $is_multifolder, rcube_utils::INPUT_GPC);
        $success_uids = [];
        $total_count = 0;

        foreach ($uids_per_folder as $folder => $uids) {
            foreach ((array)$uids as $uid) {
                $total_count++;
                if ($this->snooze_message($uid, $folder, $until)) {
                    $success_uids[] = $uid . ($is_multifolder ? '-' . $folder : '');
                }
            }
        }

        if (count($success_uids) == $total_count && $total_count > 0) {
            $rcmail->output->show_message('snoozed_messages.snoozesuccess', 'confirmation');
        } else if (count($success_uids) > 0) {
            $rcmail->output->show_message('snoozed_messages.snoozepartial', 'warning');
        } else {
            $rcmail->output->show_message('snoozed_messages.snoozefailed', 'error');
        }

        $rcmail->output->command('plugin.snooze-success', ['uids' => $success_uids]);
        $rcmail->output->send();
    }

    /**
     * Unsnooze action handler.
     */
    public function unsnooze_action()
    {
        $rcmail = $this->get_rcmail();
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_GPC);

        // Use Roundcube's standard way to get multiple UIDs
        $uids_per_folder = rcmail::get_uids(null, $mbox, $is_multifolder, rcube_utils::INPUT_GPC);
        $success_uids = [];
        $total_count = 0;

        foreach ($uids_per_folder as $folder => $uids) {
            foreach ((array)$uids as $uid) {
                $total_count++;
                if ($this->unsnooze_message($uid, $folder)) {
                    $success_uids[] = $uid . ($is_multifolder ? '-' . $folder : '');
                }
            }
        }

        if (count($success_uids) == $total_count && $total_count > 0) {
            $rcmail->output->show_message('snoozed_messages.unsnoozesuccess', 'confirmation');
        } else if (count($success_uids) > 0) {
            $rcmail->output->show_message('snoozed_messages.unsnoozepartial', 'warning');
        } else {
            $rcmail->output->show_message('snoozed_messages.unsnoozefailed', 'error');
        }

        $rcmail->output->command('plugin.snooze-success', ['uids' => $success_uids]);
        $rcmail->output->send();
    }

    /**
     * Unsnooze a single message.
     *
     * @param string $uid Message UID.
     * @param string $folder Folder name.
     * @return bool True on success, false otherwise.
     */
    public function unsnooze_message($uid, $folder)
    {
        $rcmail = $this->get_rcmail();
        $db = $rcmail->get_dbh();
        $user_id = isset($rcmail->user->ID) ? $rcmail->user->ID : 1;
        $table_name = $db->table_name('snoozed_messages');

        // MULTI-METHOD Message-ID Extraction for Unsnooze
        $message_id_header = null;
        $orig_folder = $rcmail->storage->get_folder();
        $rcmail->storage->set_folder($folder);

        // Try Method 1: Raw Headers Regex
        $raw = $rcmail->storage->get_raw_headers($uid);
        if ($raw && preg_match('/^Message-ID:\s*(<[^>]+>)/im', $raw, $m)) {
            $message_id_header = trim($m[1]);
        }

        // Try Method 2: Structured Headers
        if (empty($message_id_header)) {
            $headers = $rcmail->storage->get_message_headers($uid);
            if ($headers) {
                $message_id_header = $headers->get('message-id');
            }
        }

        // Try Method 3: Message Object
        if (empty($message_id_header)) {
            $msg = new rcube_message($uid, $folder);
            if ($msg->headers) {
                $message_id_header = $msg->headers->get('message-id');
            }
        }

        $rcmail->storage->set_folder($orig_folder);

        if (empty($message_id_header)) {
            rcube::write_log('errors', "SNOOZE: Could not retrieve Message-ID header for UID $uid in unsnooze.");
            return false;
        }

        // 1. Find the original folder and record ID in the database using the header
        $sql = "SELECT id, folder FROM $table_name WHERE message_id_header = ? AND user_id = ? LIMIT 1";
        $result = $db->query($sql, array($message_id_header, $user_id));
        $row = $db->fetch_assoc($result);
        
        $target = $row ? $row['folder'] : 'INBOX';

        // 2. Move the message back to original target folder
        if ($this->restore_message($uid, $target)) {
            // 3. Clean up the database record
            if ($row && $row['id']) {
                $db->query("DELETE FROM $table_name WHERE id = ?", array($row['id']));
            } else {
                $db->query("DELETE FROM $table_name WHERE message_id_header = ? AND user_id = ?", array($message_id_header, $user_id));
            }
            return true;
        }
        
        return false;
    }

    /**
     * Escape IMAP string.
     *
     * @param string $str String to escape.
     * @return string Escaped string.
     */
    private function _escape_imap_string($str)
    {
        return addcslashes($str, '"\\');
    }

    /**
     * Decrypt password.
     *
     * @param string $password Encrypted password.
     * @return string Decrypted password.
     */
    private function decrypt_password($password)
    {
        $rcmail = $this->get_rcmail();
        return $rcmail->decrypt($password);
    }

    /**
     * Encrypt password.
     *
     * @param string $password Decrypted password.
     * @return string Encrypted password.
     */
    private function encrypt_password($password)
    {
        $rcmail = $this->get_rcmail();
        return $rcmail->encrypt($password);
    }

    /**
     * Calculate snooze until date.
     *
     * @param string $duration Duration string.
     * @return string Formatted date string.
     */
    private function calculate_until($duration)
    {
        // Use UTC for all snooze calculations
        $now = new DateTime('now', new DateTimeZone('UTC'));
        switch ($duration) {
            case '1hour': $now->modify('+1 hour'); break;
            case 'today': $now->modify('+4 hours'); break;
            case 'tomorrow': $now->modify('tomorrow 08:00:00'); break;
            case 'weekend': $now->modify('next saturday 09:00:00'); break;
            case 'nextweek': $now->modify('next monday 08:00:00'); break;
            default:
                // Check if it's an ISO string (e.g. from custom picker) or relative string
                try {
                    $date = new DateTime($duration, new DateTimeZone('UTC'));
                    return $date->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // Fallback to default +1 day
                    $now->modify('+1 day');
                }
        }
        return $now->format('Y-m-d H:i:s');
    }

    /**
     * Get rcmail instance.
     *
     * @return rcmail Instance of rcmail.
     */
    public function get_rcmail()
    {
        return rcmail::get_instance();
    }

    /**
     * Extracts Message-ID header from a message using multiple methods.
     *
     * @param string $uid Message UID.
     * @param string $folder Folder name.
     * @return string|null Message-ID header value.
     */
    protected function get_message_id_header($uid, $folder)
    {
        $rcmail = $this->get_rcmail();
        $message_id_header = null;
        $orig_folder = $rcmail->storage->get_folder();
        
        if ($orig_folder !== $folder) {
            $rcmail->storage->set_folder($folder);
        }

        // Try Method 1: Raw Headers Regex (Most reliable for exact matches)
        $raw = $rcmail->storage->get_raw_headers($uid);
        if ($raw && preg_match('/^Message-ID:\s*(<[^>]+>)/im', $raw, $m)) {
            $message_id_header = trim($m[1]);
        }

        // Try Method 2: Structured Headers
        if (empty($message_id_header)) {
            $headers = $rcmail->storage->get_message_headers($uid);
            if ($headers) {
                $message_id_header = $headers->get('message-id');
            }
        }

        // Try Method 3: Message Object
        if (empty($message_id_header)) {
            $msg = new rcube_message($uid, $folder);
            if ($msg->headers) {
                $message_id_header = $msg->headers->get('message-id');
            }
        }

        if ($orig_folder !== $folder) {
            $rcmail->storage->set_folder($orig_folder);
        }

        return $message_id_header;
    }

    /**
     * Snooze a single message.
     *
     * @param string $uid Message UID.
     * @param string $folder Folder name.
     * @param string $until Snooze until date.
     * @return bool True on success, false otherwise.
     */
    public function snooze_message($uid, $folder, $until)
    {
        $rcmail = $this->get_rcmail();
        $db = $rcmail->get_dbh();
        $user_id = isset($rcmail->user->ID) ? $rcmail->user->ID : 1;
        $table_name = $db->table_name('snoozed_messages');

        // Extract Message-ID using refactored method
        $message_id_header = $this->get_message_id_header($uid, $folder);

        if (empty($message_id_header)) {
            rcube::write_log('errors', "SNOOZE: Could not retrieve Message-ID header for UID $uid in snooze.");
            return false;
        }

        if (!$rcmail->storage->folder_exists($this->snooze_folder)) {
            $rcmail->storage->create_folder($this->snooze_folder, true);
        }

        // Capture and encrypt password for offline redelivery
        $pass = $rcmail->decrypt($_SESSION['password']);
        $encrypted_pass = $this->encrypt_password($pass);

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $insert_result = $db->query(
            "INSERT INTO $table_name (user_id, message_id, message_id_header, encrypted_password, folder, snoozed_until, created_at) " .
            "VALUES (?, ?, ?, ?, ?, ?, ?)",
            array($user_id, $uid, $message_id_header, $encrypted_pass, $folder, $until, $now->format('Y-m-d H:i:s'))
        );

        if (!$insert_result) {
            return false;
        }

        $db_id = $db->insert_id();
        $uids_result = [];
        
        if ($rcmail->storage->move_message($uid, $this->snooze_folder, $folder, $uids_result)) {
            // Get the new UID in the snooze folder from the mapping result
            $new_uid = isset($uids_result[$uid]) ? $uids_result[$uid] : $uid;

            // Ensure new_uid is a single value
            if (is_array($new_uid)) {
                $new_uid = reset($new_uid);
            }

            // Update record with the actual new UID from the destination folder
            if ($new_uid != $uid) {
                $db->query("UPDATE $table_name SET message_id = ? WHERE id = ?", array($new_uid, $db_id));
            }
            
            return true;
        }

        // Rollback DB if IMAP move failed
        $db->query("DELETE FROM $table_name WHERE id = ?", array($db_id));
        return false;
    }

    /**
     * Hook to include assets.
     *
     * @param array $args Hook arguments.
     * @return array Updated hook arguments.
     */
    public function include_assets($args)
    {
        $this->setup_plugin_environment();
        $this->include_script('snoozed_messages.js');
        $this->include_stylesheet($this->local_skin_path() . '/snoozed_messages.css');
        
        $rcmail = $this->get_rcmail();
        $rcmail->output->set_env('snooze_folder', $this->snooze_folder);
        $rcmail->output->add_footer($this->generate_menu());
        return $args;
    }

    /**
     * Generate the snooze menu HTML.
     *
     * @return string Menu HTML.
     */
    private function generate_menu()
    {
        $rcmail = $this->get_rcmail();
        $out = '<div id="snooze-menu" class="popupmenu snooze-menu snoozed_messages_menu" aria-hidden="true">';
        $out .= '<h3 class="voice">' . $this->gettext('snooze') . '</h3>';
        $out .= '<ul class="menu listing" role="menu">';
        
        $options = [
            '1hour' => 'snooze_1hour',
            'today' => 'snooze_today',
            'tomorrow' => 'snooze_tomorrow',
            'weekend' => 'snooze_weekend',
            'nextweek' => 'snooze_nextweek',
            'custom' => 'snooze_custom',
        ];

        foreach ($options as $cmd => $label) {
            $out .= '<li role="menuitem">';
            $out .= $rcmail->output->button([
                'command'  => 'plugin.snooze-action',
                'prop'     => $cmd,
                'label'    => 'snoozed_messages.' . $label,
                'type'     => 'link-menuitem',
                'class'    => 'snooze-' . $cmd,
                'classact' => 'snooze-' . $cmd . ' active',
                'id'       => 'rcmbtn_snooze_' . $cmd,
            ]);
            $out .= '</li>';
        }
        $out .= '</ul>';
        $out .= '</div>';
        return $out;
    }
}
