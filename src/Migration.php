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

namespace Roundcube\SnoozedMessages;

class Migration
{
    private $db;

    /**
     * Constructor.
     *
     * @param rcube_db $db Database instance.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create the snoozed_messages table.
     *
     * @return mixed Query result.
     */
    public function createTable()
    {
        $table_name = $this->db->table_name('snoozed_messages');
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            message_id VARCHAR(255) NOT NULL,
            message_id_header VARCHAR(998) NOT NULL,
            encrypted_password TEXT,
            folder VARCHAR(255) NOT NULL,
            snoozed_until DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            INDEX user_id_idx (user_id),
            INDEX message_id_header_idx (message_id_header(255)),
            INDEX snoozed_until_idx (snoozed_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $result = $this->db->query($sql);

        // For existing tables, check if the new column exists
        $result = $this->db->query("SHOW COLUMNS FROM $table_name LIKE 'encrypted_password'");
        if (!$this->db->fetch_assoc($result)) {
            $this->db->query("ALTER TABLE $table_name ADD COLUMN encrypted_password TEXT AFTER message_id_header");
        }

        return $result;
    }

    /**
     * Drop the snoozed_messages table.
     *
     * @return mixed Query result.
     */
    public function dropTable()
    {
        $table_name = $this->db->table_name('snoozed_messages');
        return $this->db->query("DROP TABLE IF EXISTS $table_name");
    }
}
