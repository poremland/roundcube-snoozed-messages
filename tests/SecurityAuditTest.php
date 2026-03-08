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

class SecurityAuditTest extends TestCase
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

    /**
     * Test for potential IMAP injection in message search.
     * 
     * This test demonstrates that if a Message-ID contains double quotes, 
     * it might break the IMAP search string if not properly escaped.
     */
    public function testPotentialImapInjection()
    {
        $rcmail = rcmail::get_instance();
        
        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturn('snoozed_messages');
        $rcmail->db = $db;

        // Malicious Message-ID header containing quotes
        $malicious_header = 'test" OR SUBJECT "malicious';
        
        $db->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            [
                'id' => 1, 
                'user_id' => 1,
                'message_id' => '123', 
                'folder' => 'INBOX', 
                'message_id_header' => $malicious_header,
                'encrypted_password' => base64_encode('test')
            ],
            null
        );

        $rcmail->storage = $this->createMock(rcube_imap::class);
        $rcmail->storage->method('is_connected')->willReturn(true);
        
        // The expected search string should be: HEADER Message-ID "test\" OR SUBJECT \"malicious"
        $rcmail->storage->expects($this->once())
            ->method('search')
            ->with($this->anything(), 'HEADER Message-ID "' . addcslashes($malicious_header, '"\\') . '"');

        $this->plugin->check_expired_snoozes([]);
    }
}
