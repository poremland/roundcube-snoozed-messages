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

class ReDeliveryTest extends TestCase
{
    public function testCheckExpiredSnoozes()
    {
        require_once 'snoozed_messages.php';

        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturnMap([
            ['snoozed_messages', 'snoozed_messages'],
            ['users', 'users']
        ]);
        
        $db->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(
                [$this->stringContains('JOIN users'), $this->isType('array')],
                [$this->stringContains('DELETE FROM snoozed_messages WHERE id = ?'), array(1)]
            )
            ->willReturn(true);

        $db->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            [
                'id' => 1, 
                'user_id' => 1,
                'message_id' => '123', 
                'folder' => 'INBOX', 
                'message_id_header' => '<test@msg.id>',
                'encrypted_password' => base64_encode('testpassword')
            ],
            null
        );

        $api = $this->createMock('rcube_plugin_api');
        $plugin = $this->getMockBuilder('snoozed_messages')
            ->setConstructorArgs([$api])
            ->onlyMethods(['get_rcmail', 'restore_message'])
            ->getMock();

        $rcmail = rcmail::get_instance();
        $rcmail->db = $db;
        // Mock user object
        $rcmail->user = new stdClass();
        $rcmail->user->ID = 1;
        // Mock storage
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('is_connected')->willReturn(true);
        
        // Mock storage search
        $search_result = $this->createMock(rcube_result_set::class);
        $search_result->method('count')->willReturn(1);
        $search_result->method('get')->willReturn(['123']);
        $rcmail->storage->method('search')->willReturn($search_result);

        $plugin->method('get_rcmail')->willReturn($rcmail);
        
        // Expect restore_message to be called for the expired snooze
        $plugin->expects($this->once())
            ->method('restore_message')
            ->with('123', 'INBOX')
            ->willReturn(true);

        $plugin->check_expired_snoozes(['task' => 'mail']);
    }

    public function testRestoreMessageLogic()
    {
        require_once 'snoozed_messages.php';
        $plugin = new snoozed_messages(new rcube_plugin_api());
        $rcmail = rcmail::get_instance();

        // Mock storage
        $rcmail->storage = $this->createMock(rcube_storage::class);
        
        // Expect set_flag UNSEEN in Snoozed folder before move
        $rcmail->storage->expects($this->once())
            ->method('set_flag')
            ->with('123', 'UNSEEN', 'Snoozed')
            ->willReturn(true);

        // Expect move to INBOX from Snoozed
        $rcmail->storage->expects($this->once())
            ->method('move_message')
            ->with('123', 'INBOX', 'Snoozed')
            ->willReturn(true);

        $result = $plugin->restore_message('123', 'INBOX');
        $this->assertTrue($result);
    }
}
