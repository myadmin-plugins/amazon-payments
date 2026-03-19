<?php

namespace Detain\MyAdminAmazon\Tests;

use Detain\MyAdminAmazon\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for the Plugin class.
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass
     */
    private $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    /**
     * Tests that the Plugin class exists and can be reflected.
     * This validates the autoloading and namespace are correct.
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }

    /**
     * Tests that the Plugin class resides in the correct namespace.
     * Ensures the PSR-4 autoload mapping is working as expected.
     */
    public function testNamespace(): void
    {
        $this->assertSame('Detain\\MyAdminAmazon', $this->reflection->getNamespaceName());
    }

    /**
     * Tests that the Plugin class is not abstract and can be instantiated.
     * The constructor is empty, so instantiation should always succeed.
     */
    public function testIsInstantiable(): void
    {
        $this->assertTrue($this->reflection->isInstantiable());
    }

    /**
     * Tests that constructing a Plugin instance returns the correct type.
     * Validates the empty constructor works without error.
     */
    public function testConstructor(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Tests the static $name property has the expected value.
     * This property identifies the plugin in the system.
     */
    public function testNameProperty(): void
    {
        $this->assertSame('Amazon Plugin', Plugin::$name);
    }

    /**
     * Tests the static $description property is a non-empty string.
     * The description is displayed in plugin listings.
     */
    public function testDescriptionProperty(): void
    {
        $this->assertIsString(Plugin::$description);
        $this->assertNotEmpty(Plugin::$description);
        $this->assertStringContainsString('Amazon', Plugin::$description);
    }

    /**
     * Tests the static $help property exists and is a string.
     * The help field may be empty but must be a string type.
     */
    public function testHelpProperty(): void
    {
        $this->assertIsString(Plugin::$help);
    }

    /**
     * Tests the static $type property is set to 'plugin'.
     * This distinguishes Plugin from other component types.
     */
    public function testTypeProperty(): void
    {
        $this->assertSame('plugin', Plugin::$type);
    }

    /**
     * Tests that all four expected static properties exist on the class.
     * Uses ReflectionClass to verify the property declarations.
     */
    public function testStaticPropertiesExist(): void
    {
        $expected = ['name', 'description', 'help', 'type'];
        foreach ($expected as $prop) {
            $this->assertTrue(
                $this->reflection->hasProperty($prop),
                "Missing static property: \${$prop}"
            );
            $refProp = $this->reflection->getProperty($prop);
            $this->assertTrue($refProp->isStatic(), "\${$prop} should be static");
            $this->assertTrue($refProp->isPublic(), "\${$prop} should be public");
        }
    }

    /**
     * Tests that getHooks() returns an array.
     * The hooks array maps event names to callable references.
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Tests that getHooks() contains the 'system.settings' event hook.
     * This hook registers configuration settings for the Amazon plugin.
     */
    public function testGetHooksContainsSystemSettings(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('system.settings', $hooks);
        $this->assertSame([Plugin::class, 'getSettings'], $hooks['system.settings']);
    }

    /**
     * Tests that getHooks() contains the 'function.requirements' event hook.
     * This hook registers function requirements the plugin provides.
     */
    public function testGetHooksContainsFunctionRequirements(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('function.requirements', $hooks);
        $this->assertSame([Plugin::class, 'getRequirements'], $hooks['function.requirements']);
    }

    /**
     * Tests that getHooks() does NOT contain the 'ui.menu' hook.
     * The menu hook is commented out in the source code.
     */
    public function testGetHooksDoesNotContainUiMenu(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayNotHasKey('ui.menu', $hooks);
    }

    /**
     * Tests that all hook values are valid callable arrays with two elements.
     * Each hook must reference [ClassName, methodName].
     */
    public function testGetHooksValuesAreCallableArrays(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $event => $handler) {
            $this->assertIsArray($handler, "Handler for '{$event}' should be an array");
            $this->assertCount(2, $handler, "Handler for '{$event}' should have 2 elements");
            $this->assertSame(Plugin::class, $handler[0], "Handler class for '{$event}' should be Plugin");
            $this->assertTrue(
                method_exists($handler[0], $handler[1]),
                "Method {$handler[1]} should exist on Plugin"
            );
        }
    }

    /**
     * Tests that the getHooks() method is declared static.
     * Hook registration methods must be callable without an instance.
     */
    public function testGetHooksIsStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    /**
     * Tests that getSettings method exists, is public, and is static.
     * This method handles the 'system.settings' event.
     */
    public function testGetSettingsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Tests that getRequirements method exists, is public, and is static.
     * This method handles the 'function.requirements' event.
     */
    public function testGetRequirementsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Tests that getMenu method exists, is public, and is static.
     * This method handles the 'ui.menu' event (currently unused).
     */
    public function testGetMenuMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Tests the expected set of public methods on the Plugin class.
     * Ensures the public API surface has not changed unexpectedly.
     */
    public function testExpectedPublicMethods(): void
    {
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(function ($m) {
            return $m->getName();
        }, $methods);
        $expected = ['__construct', 'getHooks', 'getMenu', 'getRequirements', 'getSettings'];
        foreach ($expected as $name) {
            $this->assertContains($name, $methodNames, "Missing public method: {$name}");
        }
    }

    /**
     * Tests that getRequirements calls add_requirement on the loader subject.
     * Uses an anonymous class to simulate the loader without requiring the real dependency.
     */
    public function testGetRequirementsRegistersThreeFunctions(): void
    {
        $requirements = [];
        $loader = new class($requirements) {
            private $reqs;
            public function __construct(&$reqs)
            {
                $this->reqs = &$reqs;
            }
            public function add_requirement(string $name, string $path): void
            {
                $this->reqs[] = ['name' => $name, 'path' => $path];
            }
        };

        $event = new \Symfony\Component\EventDispatcher\GenericEvent($loader);
        Plugin::getRequirements($event);

        $this->assertCount(3, $requirements);

        $names = array_column($requirements, 'name');
        $this->assertContains('amazon_obtain_profile', $names);
        $this->assertContains('amazon_wallet_widget', $names);
        $this->assertContains('amazon_addressbook_widget', $names);

        foreach ($requirements as $req) {
            $this->assertStringContainsString('amazon.php', $req['path']);
        }
    }

    /**
     * Tests that getSettings calls the expected settings methods on the subject.
     * Uses an anonymous class to capture the settings registrations.
     */
    public function testGetSettingsRegistersExpectedSettings(): void
    {
        if (!defined('AMAZON_CHECKOUT_ENABLED')) {
            define('AMAZON_CHECKOUT_ENABLED', true);
        }
        if (!defined('AMAZON_SANDBOX')) {
            define('AMAZON_SANDBOX', false);
        }
        if (!defined('AMAZON_CLIENT_ID')) {
            define('AMAZON_CLIENT_ID', 'test-client-id');
        }
        if (!defined('AMAZON_SELLER_ID')) {
            define('AMAZON_SELLER_ID', 'test-seller-id');
        }

        $calls = [];
        $settings = new class($calls) {
            private $c;
            public function __construct(&$c)
            {
                $this->c = &$c;
            }
            public function add_radio_setting(...$args): void
            {
                $this->c[] = ['method' => 'add_radio_setting', 'args' => $args];
            }
            public function add_dropdown_setting(...$args): void
            {
                $this->c[] = ['method' => 'add_dropdown_setting', 'args' => $args];
            }
            public function add_text_setting(...$args): void
            {
                $this->c[] = ['method' => 'add_text_setting', 'args' => $args];
            }
            public function add_password_setting(...$args): void
            {
                $this->c[] = ['method' => 'add_password_setting', 'args' => $args];
            }
        };

        $event = new \Symfony\Component\EventDispatcher\GenericEvent($settings);
        Plugin::getSettings($event);

        $this->assertCount(4, $calls);

        $methods = array_column($calls, 'method');
        $this->assertContains('add_radio_setting', $methods);
        $this->assertContains('add_dropdown_setting', $methods);
        $this->assertContains('add_text_setting', $methods);
        $this->assertContains('add_password_setting', $methods);
    }
}
