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

class ToolbarTest extends TestCase
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

    public function testButtonsAreRegisteredInInit()
    {
        $pluginMock = $this->getMockBuilder(snoozed_messages::class)
            ->setConstructorArgs([new rcube_plugin_api()])
            ->onlyMethods(['add_button'])
            ->getMock();

        // Expect BOTH snooze and unsnooze to be registered.
        $added_buttons = [];
        $pluginMock->expects($this->atLeast(4)) // 2 in toolbar, 2 in messagemenu
            ->method('add_button')
            ->with($this->callback(function($args) use (&$added_buttons) {
                if (isset($args['command'])) {
                    $added_buttons[] = $args['command'];
                }
                return true;
            }), $this->anything());

        $pluginMock->init();

        $this->assertContains('plugin.snooze-menu', $added_buttons);
        $this->assertContains('plugin.unsnooze-action', $added_buttons);
    }
}
