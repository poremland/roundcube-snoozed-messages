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

class SnoozeTimeInListTest extends TestCase
{
    public function testMessagesListHook()
    {
        require_once 'snoozed_messages.php';

        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturn('snoozed_messages');
        
        // Mock query results
        $db->method('query')->willReturn(true);
        $db->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['message_id' => '123', 'snoozed_until' => '2026-03-10 09:00:00'],
            null
        );

        $rcmail = rcmail::get_instance();
        $rcmail->user = new stdClass();
        $rcmail->user->ID = 1;
        $rcmail->db = $db;
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('get_folder')->willReturn('Snoozed');

        $plugin = $this->getMockBuilder('snoozed_messages')
            ->disableOriginalConstructor()
            ->onlyMethods(['get_rcmail'])
            ->getMock();
        $plugin->method('get_rcmail')->willReturn($rcmail);

        // Define mock messages argument
        $msg1 = new stdClass();
        $msg1->uid = '123';
        $msg2 = new stdClass();
        $msg2->uid = '456';

        $args = [
            'messages' => [$msg1, $msg2]
        ];

        // Call the hook handler (which doesn't exist yet, so this will fail)
        $result = $plugin->messages_list_handler($args);

        // Assertions
        $this->assertObjectHasProperty('snooze_until', $result['messages'][0]);
        $this->assertEquals('2026-03-10 09:00:00', $result['messages'][0]->snooze_until);
        $this->assertObjectNotHasProperty('snooze_until', $result['messages'][1]);
    }

    public function testMessagesListHookOutsideSnoozedFolder()
    {
        require_once 'snoozed_messages.php';

        $rcmail = rcmail::get_instance();
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('get_folder')->willReturn('INBOX');

        $plugin = $this->getMockBuilder('snoozed_messages')
            ->disableOriginalConstructor()
            ->onlyMethods(['get_rcmail'])
            ->getMock();
        $plugin->method('get_rcmail')->willReturn($rcmail);

        $msg1 = new stdClass();
        $msg1->uid = '123';

        $args = [
            'messages' => [$msg1]
        ];

        // Call the hook handler
        $result = $plugin->messages_list_handler($args);

        // Assertions: no snooze_until should be added
        $this->assertObjectNotHasProperty('snooze_until', $result['messages'][0]);
    }
}
