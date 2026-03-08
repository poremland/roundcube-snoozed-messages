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

class ImapConnectionTest extends TestCase
{
    /**
     * Test that process_offline_restore SHOULD apply default SSL options when no config is provided.
     */
    public function testProcessOfflineRestoreAppliesDefaultSslOptions()
    {
        require_once 'snoozed_messages.php';

        // 1. Setup Mocks
        $api = $this->createMock('rcube_plugin_api');
        $rcmail = rcmail::get_instance();

        // Mock Config with NO imap_conn_options
        $config = $this->getMockBuilder('rcube_config')
            ->disableOriginalConstructor()
            ->getMock();

        $config->method('get')->willReturnMap([
            ['imap_host', 'ssl://127.0.0.1:993'],
            ['imap_conn_options', null]
        ]);
        $rcmail->config = $config;

        // Mock Storage
        $storage = $this->getMockBuilder('rcube_storage')
            ->disableOriginalConstructor()
            ->getMock();

        // EXPECTATION: set_options SHOULD be called with defaults
        $storage->expects($this->once())
            ->method('set_options')
            ->with($this->callback(function ($options) {
                return isset($options['ssl']) &&
                       $options['ssl']['verify_peer'] === false &&
                       $options['ssl']['verify_peer_name'] === false &&
                       $options['ssl']['allow_self_signed'] === true;
            }));

        $storage->method('connect')->willReturn(false);

        // 2. Mock Plugin - Only mock public methods we want to intercept
        $plugin = $this->getMockBuilder('snoozed_messages')
            ->setConstructorArgs([$api])
            ->onlyMethods(['get_rcmail', 'get_storage_instance'])
            ->getMock();

        $plugin->method('get_rcmail')->willReturn($rcmail);
        $plugin->method('get_storage_instance')->willReturn($storage);

        // 3. Execution
        $row = [
            'username' => 'test@example.com',
            'mail_host' => '127.0.0.1',
            'encrypted_password' => base64_encode('decrypted_pass'),
            'folder' => 'INBOX',
            'message_id_header' => '<msg@id>'
        ];

        $reflection = new ReflectionClass(get_class($plugin));
        $method = $reflection->getMethod('process_offline_restore');
        $method->setAccessible(true);

        $method->invokeArgs($plugin, [$row]);
    }
}
