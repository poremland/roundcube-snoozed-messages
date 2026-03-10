<?php
/**
 * Roundcube Snoozed Messages Plugin Version Test
 */

use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testPluginVersion()
    {
        $content = file_get_contents('snoozed_messages.php');
        $this->assertMatchesRegularExpression('/@version\s+1.1.2/', $content, 'snoozed_messages.php version should be 1.1.2');
    }

    public function testComposerVersion()
    {
        $composer = json_decode(file_get_contents('composer.json'), true);
        $this->assertEquals('1.1.2', $composer['version'], 'composer.json version should be 1.1.2');
    }

    public function testJSVersion()
    {
        $content = file_get_contents('snoozed_messages.js');
        $this->assertMatchesRegularExpression('/@version\s+1.1.2/', $content, 'snoozed_messages.js version should be 1.1.2');
    }
}
