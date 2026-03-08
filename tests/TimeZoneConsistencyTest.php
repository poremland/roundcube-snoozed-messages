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

class TimeZoneConsistencyTest extends TestCase
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

    public function testCalculateUntilUsesUTC()
    {
        $originalTz = date_default_timezone_get();
        // Set server timezone to something other than UTC to verify normalization
        date_default_timezone_set('America/Los_Angeles');
        
        $duration = '1hour';
        $method = new ReflectionMethod('snoozed_messages', 'calculate_until');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->plugin, $duration);
        
        $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
        $nowUTC->modify('+1 hour');
        
        // The result should be very close to $nowUTC (+/- 2 seconds for execution time)
        $resultDate = new DateTime($result, new DateTimeZone('UTC'));
        $diff = abs($resultDate->getTimestamp() - $nowUTC->getTimestamp());
        
        // Restore timezone
        date_default_timezone_set($originalTz);
        
        $this->assertLessThan(2, $diff, "calculate_until should return UTC time even if server is in PDT");
    }

    public function testCalculateUntilParsesIncomingISOAsUTC()
    {
        // Custom ISO string from browser (UTC)
        $iso = '2026-03-06T18:32:00.000Z';
        
        $method = new ReflectionMethod('snoozed_messages', 'calculate_until');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->plugin, $iso);
        
        $this->assertEquals('2026-03-06 18:32:00', $result, "calculate_until should parse UTC ISO strings correctly");
    }

    public function testCheckExpiredSnoozesUsesUTC()
    {
        $rcmail = rcmail::get_instance();
        $db = $this->createMock(rcube_db::class);
        $db->method('table_name')->willReturn('snoozed_messages');
        $rcmail->db = $db;

        // Mock user and storage
        $rcmail->user = new stdClass();
        $rcmail->user->ID = 1;
        $rcmail->storage = $this->createMock(rcube_storage::class);
        $rcmail->storage->method('is_connected')->willReturn(true);

        // Verify the SQL uses UTC_TIMESTAMP()
        $db->expects($this->once())
            ->method('query')
            ->with($this->stringContains('UTC_TIMESTAMP()'))
            ->willReturn(true);

        $this->plugin->check_expired_snoozes([]);
    }
}
