---
name: amazon-widget
description: Adds a new Amazon Pay helper function to src/amazon.php and registers it via Plugin::getRequirements(). Follows widget pattern (return HTML string with inline OffAmazonPayments JS) or cURL pattern for API calls. Use when user says 'add widget', 'new amazon function', 'add payment step', or extends Amazon Pay integration. Do NOT use for Plugin.php hook changes or settings additions.
---
# amazon-widget

## Critical

- All functions go in `src/amazon.php` — no new files, no namespaces, no classes.
- Widget functions MUST return a raw HTML string (not echo). Include `onError` callback in every widget.
- Always use `AMAZON_SELLER_ID` constant (not a variable) inside widget JS.
- Register every new function in `Plugin::getRequirements()` — omitting this silently breaks lazy-loading in the host app.
- Update the function count assertion in `tests/AmazonFunctionsTest.php` when adding a new function.
- Wrap all user-facing strings in `_('...')` for gettext.

## Instructions

1. **Decide the function type** — widget (returns HTML+JS) or API call (uses cURL). Check existing functions in `src/amazon.php` to confirm the naming pattern: `amazon_{noun}_{verb}()` (e.g., `amazon_wallet_widget`, `amazon_obtain_profile`).

2. **Add the function to `src/amazon.php`** after the last existing function.

   Widget pattern:
   ```php
   /**
    * @return string
    */
   function amazon_{name}_widget()
   {
       return '<div id="{name}WidgetDiv">
   </div>
   <script>
   new OffAmazonPayments.Widgets.{WidgetClass}({
   	sellerId: "'.AMAZON_SELLER_ID.'",
   	amazonOrderReferenceId: amazonOrderReferenceId,
   	design: {
   		size : {width:"400px", height:"260px"}
   	},
   	on{Event}: function(orderReference) {
   		// action after {event}
   	},
   	onError: function(error) {
   		// your error handling code
   	}
   }).bind("{name}WidgetDiv");
   </script>
   ';
   }
   ```

   cURL/API pattern:
   ```php
   function amazon_{name}()
   {
       $c = curl_init('https://api.amazon.com/{endpoint}?param='.urlencode($_REQUEST['param']));
       curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
       $r = curl_exec($c);
       curl_close($c);
       $d = json_decode($r);
       // validate $d and act on response
   }
   ```

   Verify: `php -l src/amazon.php` reports `No syntax errors`.

3. **Register the function in `src/Plugin.php::getRequirements()`** (uses output of step 2 — the exact function name string):
   ```php
   $loader->add_requirement('amazon_{name}_widget', '/../vendor/detain/myadmin-amazon-payments/src/amazon.php');
   ```
   Add this line after the last existing `add_requirement` call (~line 62). Verify: the path string must start with `/../vendor/`.

4. **Update `tests/AmazonFunctionsTest.php`**:
   - Add a new test method asserting the function is defined:
     ```php
     public function testDefinesAmazon{Name}WidgetFunction(): void
     {
         $this->assertStringContainsString('function amazon_{name}_widget()', self::$sourceContents);
     }
     ```
   - Update the function count assertion: change `assertCount(N, ...)` to the new total count.

5. **Run tests** to confirm nothing is broken:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   All tests must pass before finishing.

## Examples

**User says:** "Add a consent widget for Amazon Pay"

**Actions taken:**
1. Add to `src/amazon.php`:
   ```php
   /**
    * @return string
    */
   function amazon_consent_widget()
   {
       return '<div id="consentWidgetDiv">
   </div>
   <script>
   new OffAmazonPayments.Widgets.Consent({
   	sellerId: "'.AMAZON_SELLER_ID.'",
   	amazonOrderReferenceId: amazonOrderReferenceId,
   	design: {
   		size : {width:"400px", height:"260px"}
   	},
   	onConsent: function(orderReference) {
   		// action after buyer grants consent
   	},
   	onError: function(error) {
   		// your error handling code
   	}
   }).bind("consentWidgetDiv");
   </script>
   ';
   }
   ```
2. Add to `src/Plugin.php` in `getRequirements()`:
   ```php
   $loader->add_requirement('amazon_consent_widget', '/../vendor/detain/myadmin-amazon-payments/src/amazon.php');
   ```
3. Add `testDefinesAmazonConsentWidgetFunction()` test; update count to `4` in the function count assertion in `tests/AmazonFunctionsTest.php`.
4. Run `vendor/bin/phpunit tests/ -v` — all pass.

## Common Issues

- **Function count assertion fails**: You added a function but forgot to update the `assertCount(N, ...)` line in `tests/AmazonFunctionsTest.php`. Change the count to match the new total.
- **Function never loads in host app (undefined function error)**: Missing `$loader->add_requirement(...)` in `Plugin::getRequirements()`. The host uses lazy-loading — functions not registered here are never included.
- **Widget renders but seller ID is empty string**: `AMAZON_SELLER_ID` constant is not defined. Confirm `amazon_seller_id` is set in MyAdmin settings and `AMAZON_CHECKOUT_ENABLED` is `true`.
- **`php -l src/amazon.php` reports parse error after editing**: Heredoc-style string interpolation of constants uses `'`.CONSTANT.`'` (concatenation), not `{CONSTANT}` — the file uses standard PHP string concatenation inside single-quoted strings.
- **cURL function returns `null` from `json_decode`**: Add `curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true)` and verify the endpoint URL is correct. Check `curl_error($c)` before `curl_close`.
