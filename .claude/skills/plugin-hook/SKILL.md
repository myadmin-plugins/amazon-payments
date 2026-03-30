---
name: plugin-hook
description: Adds a new event hook to src/Plugin.php following the getHooks() registration pattern. Creates the static handler method accepting GenericEvent $event and registers it in the hooks array. Use when user says 'add hook', 'new event', 'register listener', or adds a new MyAdmin event binding. Do NOT use for modifying src/amazon.php functions or adding Composer dependencies.
---
# plugin-hook

## Critical

- Every hook handler **must** be `public static function methodName(GenericEvent $event)` — never instance methods
- Use `__CLASS__` (not `Plugin::class`) inside `getHooks()` for the handler array
- `use Symfony\Component\EventDispatcher\GenericEvent;` must already be at the top of `src/Plugin.php` — do not add it again
- Wrap all user-facing strings in `_('...')` for gettext
- If the hook should be temporarily disabled, comment it out in `getHooks()` — do not delete it

## Instructions

1. **Open `src/Plugin.php`** and read the full file to understand existing hooks and method names before touching anything.

2. **Add the hook entry to `getHooks()`** — append a new key-value pair to the returned array:
   ```php
   public static function getHooks()
   {
       return [
           'system.settings'      => [__CLASS__, 'getSettings'],
           'function.requirements'=> [__CLASS__, 'getRequirements'],
           'your.event.name'      => [__CLASS__, 'yourMethodName'],  // ← new entry
       ];
   }
   ```
   Event name format: `noun.verb` (e.g. `billing.invoice`, `ui.menu`, `vps.cancel`).
   Verify the event name matches exactly what `run_event()` dispatches in the main codebase.

3. **Add the static handler method** below the last existing handler, following this exact signature and body structure:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function yourMethodName(GenericEvent $event)
   {
       $subject = $event->getSubject();
       // handler logic here
   }
   ```
   - `$event->getSubject()` returns the subject passed to `run_event()` — cast/type-hint it in a `/** @var \SomeClass $subject */` doc comment if the type is known.
   - For `function.requirements` style hooks: call `$subject->add_requirement('fn_name', '/../vendor/.../src/file.php')`
   - For `system.settings` style hooks: call `$subject->add_*_setting(...)` methods
   - For `ui.menu` style hooks: check `$GLOBALS['tf']->ima == 'admin'` before mutating the menu

4. **Run tests** to verify nothing is broken:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   Verify all existing tests still pass before writing new ones.

5. **Add a test for the new hook** in `tests/PluginTest.php`:
   - Assert `getHooks()` contains the new event key mapped to `[Plugin::class, 'yourMethodName']`
   - Assert the method is `public` and `static` via `ReflectionClass`
   - Assert it accepts exactly one parameter named `event`
   - Use an anonymous class as the event subject to simulate the real subject without real dependencies (see `testGetRequirementsRegistersThreeFunctions` for the pattern)

## Examples

**User says:** "Add a hook for the `billing.invoice` event that logs the invoice ID"

**Actions taken:**
1. Read `src/Plugin.php`
2. Add `'billing.invoice' => [__CLASS__, 'onBillingInvoice']` to `getHooks()`
3. Add method:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function onBillingInvoice(GenericEvent $event)
   {
       $invoice = $event->getSubject();
       // handle invoice event
   }
   ```
4. Run `vendor/bin/phpunit tests/ -v`
5. Add `testGetHooksContainsBillingInvoice()` and `testOnBillingInvoiceMethodSignature()` to `tests/PluginTest.php`

**Result:** Hook registered, handler implemented, tests green.

## Common Issues

- **"Call to undefined method" at runtime**: The event name in `getHooks()` doesn't match what `run_event()` dispatches. Search the main codebase for `run_event('your.event.name'` to confirm the exact string.
- **Test fails with "Method yourMethodName should exist on Plugin"** (from `testGetHooksValuesAreCallableArrays`): Method name in `getHooks()` array doesn't match the actual method name defined. Check for typos — PHP method names are case-insensitive but array strings are not.
- **`GenericEvent` not found in tests**: `tests/bootstrap.php` loads autoload which includes `symfony/event-dispatcher`. Run `composer install` if the class is missing.
- **Existing test `testExpectedPublicMethods` fails**: That test hardcodes expected method names. Add your new method name to its `$expected` array in `tests/PluginTest.php`.
- **Hook fires but subject is wrong type**: `$event->getSubject()` returns whatever was passed as the first arg to `new GenericEvent(...)`. Add a `/** @var \ExpectedClass $subject */` docblock and verify the dispatching code in the main repo.
