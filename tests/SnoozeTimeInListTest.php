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
    protected function setUp(): void
    {
        rcmail::reset_instance();
        rcube_utils::$inputs = [];
    }

    protected function tearDown(): void
    {
        rcube_utils::$inputs = [];
    }

    public function testMessagesListHook()
    {
        require_once 'snoozed_messages.php';

        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturn('snoozed_messages');
        
        // Mock query results
        $db->method('query')->willReturn(new stdClass());

        $rcmail = rcmail::get_instance();
        $rcmail->user = new stdClass();
        $rcmail->user->ID = 1;
        $rcmail->db = $db;
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('get_folder')->willReturn('Snoozed');
        
        rcube_utils::$inputs['_mbox'] = 'Snoozed';

        $plugin = $this->getMockBuilder('snoozed_messages')
            ->disableOriginalConstructor()
            ->onlyMethods(['get_rcmail', 'get_message_id_header'])
            ->getMock();
        $plugin->method('get_rcmail')->willReturn($rcmail);
        
        // Mock ID extraction
        $plugin->method('get_message_id_header')->willReturnCallback(function($uid, $folder) {
            return $uid === '123' ? '<msg123@id>' : '<msg456@id>';
        });

        // Mock query results
        $db->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['user_id' => 1, 'message_id' => '123', 'message_id_header' => '<msg123@id>', 'snoozed_until' => '2026-03-10 09:00:00'],
            null
        );
        $msg1 = new stdClass();
        $msg1->uid = '123';
        $msg2 = new stdClass();
        $msg2->uid = '456';

        $args = [
            'messages' => [$msg1, $msg2]
        ];

        // Call the hook handler
        $result = $plugin->messages_list_handler($args);

        // Assertions: verify data passed to client via env
        $snooze_data = isset($rcmail->output->env['snooze_data']) ? $rcmail->output->env['snooze_data'] : null;
        $this->assertIsArray($snooze_data);
        $this->assertArrayHasKey('123', $snooze_data);
        $this->assertEquals('2026-03-10 09:00:00', $snooze_data['123']);
        $this->assertArrayNotHasKey('456', $snooze_data);
    }

    public function testMessagesListHookOutsideSnoozedFolder()
    {
        require_once 'snoozed_messages.php';
        
        rcube_utils::$inputs['_mbox'] = 'INBOX';

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

        // Assertions: no snooze_data should be added to env
        $this->assertTrue(empty($rcmail->output->env['snooze_data']));
    }
}
