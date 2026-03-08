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

class UnsnoozeActionTest extends TestCase
{
    /**
     * @var snoozed_messages
     */
    private $plugin;

    protected function setUp(): void
    {
        require_once 'snoozed_messages.php';
        $this->plugin = new snoozed_messages(new rcube_plugin_api());
        rcube_utils::$inputs = [];
    }

    public function testUnsnoozeActionHandler()
    {
        // Mock input values
        rcube_utils::$inputs['_uid'] = '123';
        rcube_utils::$inputs['_mbox'] = 'Snoozed';
        
        $pluginMock = $this->getMockBuilder(snoozed_messages::class)
            ->setConstructorArgs([new rcube_plugin_api()])
            ->onlyMethods(['unsnooze_message'])
            ->getMock();

        $pluginMock->expects($this->once())
            ->method('unsnooze_message')
            ->with('123', 'Snoozed')
            ->willReturn(true);
        
        $pluginMock->unsnooze_action();
    }

    public function testUnsnoozeMessageLogic()
    {
        $rcmail = rcmail::get_instance();
        
        // Mock DB query for lookup and deletion
        $rcmail->db = $this->createMock(rcube_db::class);
        $rcmail->db->method('table_name')->willReturn('snoozed_messages');
        
        // Lookup and Delete queries both use user_id now, passed as arrays
        $rcmail->db->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(
                [$this->stringContains('SELECT id, folder'), array('<test@msg.id>', 1)],
                [$this->stringContains('DELETE FROM snoozed_messages WHERE id = ?'), array(1)]
            )
            ->willReturn(true);

        $rcmail->db->method('fetch_assoc')->willReturn(['id' => 1, 'folder' => 'INBOX']);

        // Mock storage move and headers
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('get_folder')->willReturn('Snoozed');
        $rcmail->storage->method('get_raw_headers')->willReturn("Message-ID: <test@msg.id>\r\n");
        $rcmail->storage->expects($this->once())
            ->method('move_message')
            ->with('123', 'INBOX', 'Snoozed')
            ->willReturn(true);

        $result = $this->plugin->unsnooze_message('123', 'Snoozed');
        $this->assertTrue($result);
    }
}
