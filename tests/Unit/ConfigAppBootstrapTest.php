<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * config/app.php must start with a PHP open tag only. A stray prefix before
 * <?php is emitted as raw output on every config load, which corrupts PDF
 * fee receipts, redirects, and session cookies (headers already sent).
 */
class ConfigAppBootstrapTest extends TestCase
{
    private function configAppPath(): string
    {
        return dirname(__DIR__, 2) . '/config/app.php';
    }

    public function test_config_app_starts_with_php_open_tag(): void
    {
        $raw = file_get_contents($this->configAppPath());
        $this->assertNotFalse($raw);
        $this->assertMatchesRegularExpression('/^\s*<\?php\b/', $raw);
        $this->assertStringNotContainsString("cv   <?php", $raw);
    }

    public function test_loading_config_app_emits_no_output(): void
    {
        if (!function_exists('env')) {
            // Stub Laravel helper so the config file can be included standalone.
            eval('function env($key, $default = null) { return $default; }');
        }

        ob_start();
        $config = include $this->configAppPath();
        $leaked = ob_get_clean();

        $this->assertSame('', $leaked, 'config/app.php must not emit output before the returned array');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('name', $config);
    }
}
