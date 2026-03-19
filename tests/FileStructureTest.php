<?php

namespace Detain\MyAdminAmazon\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the overall package file structure and configuration.
 */
class FileStructureTest extends TestCase
{
    /**
     * @var string
     */
    private static $packageRoot;

    public static function setUpBeforeClass(): void
    {
        self::$packageRoot = dirname(__DIR__);
    }

    /**
     * Tests that composer.json exists in the package root.
     * Every Composer package must have this file.
     */
    public function testComposerJsonExists(): void
    {
        $this->assertFileExists(self::$packageRoot . '/composer.json');
    }

    /**
     * Tests that composer.json contains valid JSON.
     * Invalid JSON would prevent the package from being installed.
     */
    public function testComposerJsonIsValidJson(): void
    {
        $content = file_get_contents(self::$packageRoot . '/composer.json');
        $data = json_decode($content, true);
        $this->assertNotNull($data, 'composer.json is not valid JSON');
    }

    /**
     * Tests the package name in composer.json matches expectations.
     * This must match the Packagist registration.
     */
    public function testComposerJsonPackageName(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertSame('detain/myadmin-amazon-payments', $data['name']);
    }

    /**
     * Tests that composer.json has a PSR-4 autoload entry for the correct namespace.
     * This enables Composer to autoload the Plugin class.
     */
    public function testComposerJsonAutoload(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('autoload', $data);
        $this->assertArrayHasKey('psr-4', $data['autoload']);
        $this->assertArrayHasKey('Detain\\MyAdminAmazon\\', $data['autoload']['psr-4']);
        $this->assertSame('src/', $data['autoload']['psr-4']['Detain\\MyAdminAmazon\\']);
    }

    /**
     * Tests that the src directory exists.
     * All source code lives under src/ per the PSR-4 mapping.
     */
    public function testSrcDirectoryExists(): void
    {
        $this->assertDirectoryExists(self::$packageRoot . '/src');
    }

    /**
     * Tests that Plugin.php exists in the src directory.
     * This is the main class of the package.
     */
    public function testPluginPhpExists(): void
    {
        $this->assertFileExists(self::$packageRoot . '/src/Plugin.php');
    }

    /**
     * Tests that amazon.php exists in the src directory.
     * This file contains the procedural Amazon helper functions.
     */
    public function testAmazonPhpExists(): void
    {
        $this->assertFileExists(self::$packageRoot . '/src/amazon.php');
    }

    /**
     * Tests that the README.md file exists.
     * A README is expected for any published package.
     */
    public function testReadmeExists(): void
    {
        $this->assertFileExists(self::$packageRoot . '/README.md');
    }

    /**
     * Tests that Plugin.php is valid PHP by linting it.
     * Catches syntax errors before runtime.
     */
    public function testPluginPhpIsValidPhp(): void
    {
        $file = self::$packageRoot . '/src/Plugin.php';
        $result = shell_exec('php -l ' . escapeshellarg($file) . ' 2>&1');
        $this->assertStringContainsString('No syntax errors', $result);
    }

    /**
     * Tests that composer.json specifies a license.
     * Open source packages should declare their license for legal clarity.
     */
    public function testComposerJsonHasLicense(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('license', $data);
        $this->assertNotEmpty($data['license']);
    }

    /**
     * Tests that composer.json has a description.
     * The description appears on Packagist and in composer search results.
     */
    public function testComposerJsonHasDescription(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('description', $data);
        $this->assertNotEmpty($data['description']);
    }

    /**
     * Tests that the package type is myadmin-plugin.
     * This custom type is used by the MyAdmin plugin installer.
     */
    public function testComposerJsonType(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertSame('myadmin-plugin', $data['type']);
    }

    /**
     * Tests that composer.json requires PHP.
     * The PHP version constraint ensures compatibility.
     */
    public function testComposerJsonRequiresPhp(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('php', $data['require']);
    }

    /**
     * Tests that composer.json requires the symfony/event-dispatcher.
     * The Plugin class depends on GenericEvent from this package.
     */
    public function testComposerJsonRequiresEventDispatcher(): void
    {
        $data = json_decode(file_get_contents(self::$packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('symfony/event-dispatcher', $data['require']);
    }
}
