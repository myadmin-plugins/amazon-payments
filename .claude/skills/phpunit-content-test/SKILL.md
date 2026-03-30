---
name: phpunit-content-test
description: Writes a PHPUnit test class using the content-assertion pattern from `tests/AmazonFunctionsTest.php` — loads source via `file_get_contents()` in `setUpBeforeClass()` then asserts with `assertStringContainsString()`. Use when user says 'add test', 'write tests for', or adds new functions to `src/amazon.php`. Do NOT use for ReflectionClass-based structural tests (see `tests/PluginTest.php` pattern).
---
# PHPUnit Content Test

## Critical

- **Never invoke the functions under test directly** — they depend on `curl`, global `$_REQUEST`, and undefined constants. Test via `file_get_contents()` static analysis only.
- `tests/bootstrap.php` must be used as the bootstrap — it defines the `_()` gettext stub before autoload. Do not add a second bootstrap.
- Class must live in `tests/` with namespace `Detain\MyAdminAmazon\Tests`.
- Every test method must be `public function testXxx(): void` (no return type omission).

## Instructions

1. **Create the test file** at `tests/{ClassName}Test.php`. Name it after the source file under test (e.g., a test for `src/amazon.php` goes in `tests/AmazonTest.php`).

2. **Add the class skeleton** — copy this exactly:
   ```php
   <?php
   
   namespace Detain\MyAdminAmazon\Tests;
   
   use PHPUnit\Framework\TestCase;
   
   class <ClassName>Test extends TestCase
   {
       private static $sourceFile;
       private static $sourceContents;
   
       public static function setUpBeforeClass(): void
       {
           self::$sourceFile = dirname(__DIR__) . '/src/<filename>.php';
           self::$sourceContents = file_get_contents(self::$sourceFile);
       }
   }
   ```
   Verify `dirname(__DIR__) . '/src/<filename>.php'` resolves to the actual file before proceeding.

3. **Add the mandatory baseline tests** — always include these three for any new source file:
   ```php
   public function testSourceFileExists(): void
   {
       $this->assertFileExists(self::$sourceFile);
   }
   
   public function testSourceFileIsValidPhp(): void
   {
       $result = shell_exec('php -l ' . escapeshellarg(self::$sourceFile) . ' 2>&1');
       $this->assertStringContainsString('No syntax errors', $result, 'PHP lint failed: ' . $result);
   }
   
   public function testSourceFileContainsExactlyNFunctions(): void
   {
       preg_match_all('/^\s*function\s+\w+\s*\(/m', self::$sourceContents, $matches);
       $this->assertCount(<N>, $matches[0], 'Expected exactly <N> function definitions');
   }
   ```
   Replace `<N>` with the actual count of functions in the source file.

4. **Add a function-definition test per function** — one per public function in the source:
   ```php
   public function testDefines<FunctionName>Function(): void
   {
       $this->assertStringContainsString('function <function_name>()', self::$sourceContents);
   }
   ```

5. **Add content-assertion tests for key implementation details** — assert literal strings that must appear in the source. Use `assertStringContainsString()` for single occurrences; use `assertGreaterThanOrEqual(N, substr_count(...))` when a string must appear multiple times:
   ```php
   // Single occurrence
   $this->assertStringContainsString('expected_literal', self::$sourceContents);
   
   // Must appear at least N times
   $this->assertGreaterThanOrEqual(
       2,
       substr_count(self::$sourceContents, 'repeated_literal'),
       'repeated_literal should appear at least twice'
   );
   ```

6. **Register the test file** in `phpunit.xml.dist` if it is not already covered by the `<directory>` glob. Verify with:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   All new tests must pass with no errors or skips.

## Examples

**User says:** "Write tests for the new `amazon_button_widget()` function I added to `src/amazon.php`."

**Actions taken:**
1. Read `src/amazon.php` to identify the literal strings and constants `amazon_button_widget()` uses.
2. Add to `tests/AmazonFunctionsTest.php` (the existing test class for `src/amazon.php`):
   ```php
   public function testDefinesAmazonButtonWidgetFunction(): void
   {
       $this->assertStringContainsString('function amazon_button_widget()', self::$sourceContents);
   }
   
   public function testButtonWidgetContainsCorrectJsClass(): void
   {
       $this->assertStringContainsString('OffAmazonPayments.Widgets.Button', self::$sourceContents);
   }
   
   public function testSourceFileContainsExactlyFourFunctions(): void
   {
       preg_match_all('/^\s*function\s+\w+\s*\(/m', self::$sourceContents, $matches);
       $this->assertCount(4, $matches[0], 'Expected exactly 4 function definitions in amazon.php');
   }
   ```
3. Run `vendor/bin/phpunit tests/ -v` — all tests green.

**Result:** New test methods appended to the existing class, function count assertion updated from 3 → 4.

## Common Issues

- **`assertFileExists` fails:** The path `dirname(__DIR__) . '/src/amazon.php'` assumes the test file is in `tests/`. If you placed the test elsewhere, adjust the `dirname` depth.
- **`No syntax errors` assertion fails with `php: command not found`:** PHP is not on `$PATH` in the test runner. Use the full path: `shell_exec('/usr/bin/php -l ...')`.
- **`assertCount(3, ...)` fails with count mismatch:** The source file gained or lost a function. Update `<N>` to match the current function count, then verify with `grep -c 'function ' src/amazon.php`.
- **`assertStringContainsString` fails on a string with single quotes:** PHP strings with `'` in the source may need the assertion string to use escaped double-quote notation — copy the literal exactly from `src/amazon.php` output via `file_get_contents`.
- **Class not found / autoload error:** Namespace must be `Detain\MyAdminAmazon\Tests` and file must be in `tests/`. Check `composer.json` `autoload-dev` section maps `Detain\MyAdminAmazon\Tests\` → `tests/`.
