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

class EncryptedVaultTest extends TestCase
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

    public function testEncryptionDecryption()
    {
        $password = 'secret-password-123';
        
        $method_encrypt = new ReflectionMethod('snoozed_messages', 'encrypt_password');
        $method_encrypt->setAccessible(true);
        $encrypted = $method_encrypt->invoke($this->plugin, $password);
        
        $this->assertNotEquals($password, $encrypted);
        
        $method_decrypt = new ReflectionMethod('snoozed_messages', 'decrypt_password');
        $method_decrypt->setAccessible(true);
        $decrypted = $method_decrypt->invoke($this->plugin, $encrypted);
        
        $this->assertEquals($password, $decrypted);
    }

    public function testOfflineRedeliveryTriggeredByCLI()
    {
        $rcmail = rcmail::get_instance();
        $db = $this->createMock('rcube_db');
        $db->method('table_name')->willReturnMap([
            ['snoozed_messages', 'snoozed_messages'],
            ['users', 'users']
        ]);
        
        // Mock a result from the DB
        $db->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            [
                'id' => 5, 
                'user_id' => 2,
                'username' => 'offline-user',
                'mail_host' => 'imap.test.com',
                'message_id' => 'abc', 
                'folder' => 'INBOX', 
                'message_id_header' => '<offline@msg.id>',
                'encrypted_password' => base64_encode('offline-pass')
            ],
            null
        );

        $rcmail->db = $db;
        $rcmail->user = null; // No active user
        
        // Mock config for host resolution and options
        $rcmail->config = $this->createMock(rcube_config::class);
        $rcmail->config->method('get')->willReturnMap([
            ['imap_host', null, 'ssl://imap.test.com:993'],
            ['imap_conn_options', null, []]
        ]);
        
        // Mock storage
        $storage = $this->createMock(rcube_imap::class);
        $search_result = $this->createMock(rcube_result_set::class);
        $search_result->method('count')->willReturn(1);
        $search_result->method('get')->willReturn(['abc']);
        $storage->method('search')->willReturn($search_result);

        // Expect manual IMAP connect with the host from config (parsed)
        $storage->expects($this->once())
            ->method('connect')
            ->with('imap.test.com', 'offline-user', 'offline-pass', 993, 'ssl')
            ->willReturn(true);

        $storage->expects($this->once())
            ->method('move_message')
            ->with('abc', 'INBOX', 'Snoozed')
            ->willReturn(true);

        // Mock the plugin to return our storage mock
        $plugin = $this->getMockBuilder('snoozed_messages')
            ->setConstructorArgs([new rcube_plugin_api()])
            ->onlyMethods(['get_storage_instance', 'get_rcmail'])
            ->getMock();
        
        $plugin->method('get_storage_instance')->willReturn($storage);
        $plugin->method('get_rcmail')->willReturn($rcmail);

        $plugin->check_expired_snoozes([]);
    }
}
