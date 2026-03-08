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

use PHPUnit\Framework\TestCase;

class MultiMessageTest extends TestCase
{
    /**
     * @var snoozed_messages
     */
    private $plugin;

    protected function setUp(): void
    {
        require_once 'snoozed_messages.php';
        $this->plugin = new snoozed_messages(new rcube_plugin_api());
    }

    public function testSnoozeActionWithMultipleUIDs()
    {
        $rcmail = rcmail::get_instance();
        
        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturn('snoozed_messages');
        // Expect 2 inserts and 2 potential updates
        $db->expects($this->atLeast(2))
            ->method('query')
            ->willReturn(true);
        $db->method('insert_id')->willReturn(1);
        $rcmail->db = $db;

        $_SESSION['password'] = base64_encode('testpassword');
        rcube_utils::$inputs['_uid'] = '123,456';
        rcube_utils::$inputs['_mbox'] = 'INBOX';
        rcube_utils::$inputs['_duration'] = '1hour';

        $rcmail->storage = $this->createMock(rcube_imap::class);
        $rcmail->storage->method('folder_exists')->willReturn(true);
        $rcmail->storage->method('get_folder')->willReturn('INBOX');
        $rcmail->storage->method('get_raw_headers')->willReturn("Message-ID: <test@msg.id>\r\n");
        $rcmail->storage->method('move_message')->willReturn(true);

        // Mock output to verify successful command
        $rcmail->output = $this->createMock(rcmail_output_html::class);
        $rcmail->output->expects($this->once())
            ->method('command')
            ->with('plugin.snooze-success', $this->callback(function($args) {
                return count($args['uids']) === 2 && in_array('123', $args['uids']) && in_array('456', $args['uids']);
            }));

        $this->plugin->snooze_action();
    }

    public function testUnsnoozeActionWithMultipleUIDs()
    {
        $rcmail = rcmail::get_instance();
        
        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturn('snoozed_messages');
        $db->method('query')->willReturn(true);
        $db->method('fetch_assoc')->willReturn(['id' => 1, 'folder' => 'INBOX']);
        $rcmail->db = $db;

        rcube_utils::$inputs['_uid'] = '123,456';
        rcube_utils::$inputs['_mbox'] = 'Snoozed';

        $rcmail->storage = $this->createMock(rcube_imap::class);
        $rcmail->storage->method('get_folder')->willReturn('Snoozed');
        $rcmail->storage->method('get_raw_headers')->willReturn("Message-ID: <test@msg.id>\r\n");
        $rcmail->storage->method('move_message')->willReturn(true);

        // Mock output to verify successful command
        $rcmail->output = $this->createMock(rcmail_output_html::class);
        $rcmail->output->expects($this->once())
            ->method('command')
            ->with('plugin.snooze-success', $this->callback(function($args) {
                return count($args['uids']) === 2 && in_array('123', $args['uids']) && in_array('456', $args['uids']);
            }));

        $this->plugin->unsnooze_action();
    }
}
