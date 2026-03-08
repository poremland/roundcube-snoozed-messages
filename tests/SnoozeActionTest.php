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

class SnoozeActionTest extends TestCase
{
    public function testSnoozeAction()
    {
        require_once 'snoozed_messages.php';

        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturn('snoozed_messages');
        $db->expects($this->atLeastOnce())
            ->method('query')
            ->willReturn(true);
        $db->method('insert_id')->willReturn(1);

        $api = $this->getMockBuilder('rcube_plugin_api')
            ->onlyMethods(['add_content', 'include_script', 'include_stylesheet'])
            ->getMock();

        $plugin = $this->getMockBuilder('snoozed_messages')
            ->setConstructorArgs([$api])
            ->onlyMethods(['add_texts', 'local_skin_path', 'get_rcmail'])
            ->getMock();

        $rcmail = rcmail::get_instance();
        // Mock the user object which is used in snooze_message
        $rcmail->user = new stdClass();
        $rcmail->user->ID = 1;
        $rcmail->db = $db;
        
        $_SESSION['password'] = base64_encode('testpassword');
        
        // Mock storage move and search
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('folder_exists')->willReturn(true);
        $rcmail->storage->method('get_folder')->willReturn('INBOX');
        $rcmail->storage->method('get_raw_headers')->willReturn("Message-ID: <test@msg.id>\r\n");
        $rcmail->storage->method('move_message')->willReturn(true);
        $rcmail->storage->method('get_message_headers')->willReturn(null);
        $rcmail->storage->method('search')->willReturn(null);

        $plugin->method('get_rcmail')->willReturn($rcmail);

        // Mock the actual snooze logic
        $result = $plugin->snooze_message('123', 'INBOX', '2026-03-05 12:00:00');
        
        $this->assertTrue($result);
    }
}
