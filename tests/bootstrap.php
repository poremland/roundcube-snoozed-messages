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
 */

// Include Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

$_SESSION = [];

// Consolidated Roundcube Mock Classes
if (!class_exists('rcube_plugin')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_plugin {
        public $api;
        public function __construct($api = null) { $this->api = $api; }
        public function add_hook($hook, $handler) {}
        public function add_texts($path) {}
        public function gettext($name) { return $name; }
        public function register_action($action, $handler) {}
        public function add_button($args, $container) {}
        public function add_gui_object($name, $id) {}
        public function include_script($name) { if ($this->api) $this->api->include_script($name); }
        public function include_stylesheet($name) { if ($this->api) $this->api->include_stylesheet($name); }
        public function local_skin_path() { return 'skins/elastic'; }
    }
}

if (!class_exists('rcube_plugin_api')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_plugin_api {
        public function add_content($content, $target) {}
        public function include_script($name) {}
        public function include_stylesheet($name) {}
    }
}

if (!class_exists('rcmail_output_html')) {
    /**
     * Mock class for testing purposes.
     */
    class rcmail_output_html {
        public $type = 'html';
        public $env = [];
        public function set_env($key, $val) { $this->env[$key] = $val; }
        public function button($args = []) { return ""; }
        public function show_message($message, $type) {}
        public function command($command, $data = []) {}
        public function send() {}
        public function add_footer($html) {}
        public function add_gui_object($name, $id) {}
    }
}

if (!class_exists('rcube_storage')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_storage {
        public $current_folder = 'INBOX';
        public function get_folder() { return $this->current_folder; }
        public function set_folder($name) { $this->current_folder = $name; }
        public function is_connected() { return true; }
        public function folder_exists($name, $subscribed = false) { return true; }
        public function create_folder($name, $subscribe = false) { return true; }
        public function subscribe($name) { return true; }
        public function move_message($uids, $target_folder, $source_folder = null, &$uids_result = null) { return true; }
        public function get_uids($mbox = null) { return []; }
        public function get_message_headers($uid, $folder = null) { return null; }
        public function get_raw_headers($uid, $folder = null) { return ""; }
        public function get_message($uid, $folder = null) { return null; }
        public function search($mbox, $criteria) { return null; }
        public function set_flag($uids, $flag, $folder = null) { return true; }
        public function connect($host, $user, $pass, $port = 143, $use_ssl = null) { return true; }
        public function imap_connect($user, $pass, $host) { return true; }
        public function get_error_code() { return 0; }
        public function get_error_str() { return ""; }
        public function set_options($opt) {}
        public function close() {}
        public function get_user() { return 1; }
    }
}

if (!class_exists('rcube_imap')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_imap extends rcube_storage {}
}

if (!class_exists('rcube_message')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_message {
        public $headers;
        public function __construct($uid, $folder = null) {}
    }
}

if (!class_exists('rcube_result_set')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_result_set {
        public function count() { return 0; }
        public function get() { return []; }
    }
}

if (!class_exists('rcube_config')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_config {
        public function get($name, $default = null) { return $default; }
    }
}

if (!class_exists('rcmail')) {
    /**
     * Mock class for testing purposes.
     */
    class rcmail {
        private static $instance;
        public $output;
        public $user;
        public $db;
        public $storage;
        public $config;
        public function __construct() {
            $this->output = new rcmail_output_html();
            $this->user = new stdClass();
            $this->user->ID = 1;
            $this->storage = new rcube_storage();
            $this->config = $this->create_config_mock();
        }
        private function create_config_mock() {
            return new class {
                public function get($name, $default = null) {
                    if ($name === 'imap_host') return 'ssl://localhost:993';
                    return $default;
                }
            };
        }
        public static function get_instance() {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function get_dbh() { return $this->db ?: new rcube_db(); }
        public function get_storage() { return $this->storage ?: new rcube_storage(); }
        public static function gettext($name) { return $name; }
        public function encrypt($str) { return base64_encode($str); }
        public function decrypt($str) { return base64_decode($str); }
        public static function get_uids($uids = null, $mbox = null, &$is_multifolder = false, $mode = null) {
            $is_multifolder = false;
            $uids = $uids ?: rcube_utils::get_input_value('_uid', $mode ?: rcube_utils::INPUT_GPC);
            $mbox = $mbox ?: rcube_utils::get_input_value('_mbox', $mode ?: rcube_utils::INPUT_GPC);
            
            if (is_string($uids)) {
                $uids = explode(',', $uids);
            }
            
            return [$mbox => (array)$uids];
        }
    }
}

if (!class_exists('rcube_db')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_db {
        public function query($sql, $params = []) { return true; }
        public function fetch_assoc($result) {}
        public function table_name($name) { return $name; }
        public function insert_id($sequence = null) { return 1; }
        public function array2list($arr) {
            return "'" . implode("','", array_map('addslashes', $arr)) . "'";
        }
    }
}

if (!class_exists('rcube')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube {
        public static function write_log($name, $message) {}
        public function encrypt($str) { return base64_encode($str); }
        public function decrypt($str) { return base64_decode($str); }
        public static function get_instance() { return rcmail::get_instance(); }
    }
}

if (!class_exists('rcube_utils')) {
    /**
     * Mock class for testing purposes.
     */
    class rcube_utils {
        const INPUT_GPC = 1;
        public static $inputs = [];
        public static function get_input_value($name, $mode) { return self::$inputs[$name] ?? null; }
    }
}
