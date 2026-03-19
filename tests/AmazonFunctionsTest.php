<?php

namespace Detain\MyAdminAmazon\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the procedural functions in amazon.php.
 *
 * Since the functions rely on curl, global $_REQUEST, and constants like
 * AMAZON_SELLER_ID, we test them via static analysis of the source file
 * rather than direct invocation.
 */
class AmazonFunctionsTest extends TestCase
{
    /**
     * @var string
     */
    private static $sourceFile;

    /**
     * @var string
     */
    private static $sourceContents;

    public static function setUpBeforeClass(): void
    {
        self::$sourceFile = dirname(__DIR__) . '/src/amazon.php';
        self::$sourceContents = file_get_contents(self::$sourceFile);
    }

    /**
     * Tests that the amazon.php source file exists on disk.
     * This is the file that provides all three Amazon helper functions.
     */
    public function testSourceFileExists(): void
    {
        $this->assertFileExists(self::$sourceFile);
    }

    /**
     * Tests that the source file is valid PHP with no syntax errors.
     * Uses php -l (lint) to verify the file parses correctly.
     */
    public function testSourceFileIsValidPhp(): void
    {
        $output = [];
        $exitCode = 0;
        $cmd = 'php -l ' . escapeshellarg(self::$sourceFile) . ' 2>&1';
        $result = shell_exec($cmd);
        $this->assertStringContainsString('No syntax errors', $result, 'PHP lint failed: ' . $result);
    }

    /**
     * Tests that the amazon_obtain_profile function is defined in the source.
     * This function handles OAuth token verification with Amazon's API.
     */
    public function testDefinesAmazonObtainProfileFunction(): void
    {
        $this->assertStringContainsString('function amazon_obtain_profile()', self::$sourceContents);
    }

    /**
     * Tests that the amazon_addressbook_widget function is defined in the source.
     * This function returns HTML/JS for the Amazon address book widget.
     */
    public function testDefinesAmazonAddressbookWidgetFunction(): void
    {
        $this->assertStringContainsString('function amazon_addressbook_widget()', self::$sourceContents);
    }

    /**
     * Tests that the amazon_wallet_widget function is defined in the source.
     * This function returns HTML/JS for the Amazon wallet payment widget.
     */
    public function testDefinesAmazonWalletWidgetFunction(): void
    {
        $this->assertStringContainsString('function amazon_wallet_widget()', self::$sourceContents);
    }

    /**
     * Tests that amazon_obtain_profile uses the correct Amazon token API endpoint.
     * The function must validate access tokens against this URL.
     */
    public function testObtainProfileUsesTokenInfoEndpoint(): void
    {
        $this->assertStringContainsString(
            'https://api.amazon.com/auth/o2/tokeninfo',
            self::$sourceContents
        );
    }

    /**
     * Tests that amazon_obtain_profile uses the sandbox user profile endpoint.
     * This fetches user profile data after token validation.
     */
    public function testObtainProfileUsesSandboxProfileEndpoint(): void
    {
        $this->assertStringContainsString(
            'https://api.sandbox.amazon.com/user/profile',
            self::$sourceContents
        );
    }

    /**
     * Tests that amazon_obtain_profile reads access_token from the request.
     * The function depends on $_REQUEST['access_token'].
     */
    public function testObtainProfileReadsAccessToken(): void
    {
        $this->assertStringContainsString("\$_REQUEST['access_token']", self::$sourceContents);
    }

    /**
     * Tests that the obtain profile function uses curl for HTTP requests.
     * Both API calls in the function use curl_init/curl_exec/curl_close.
     */
    public function testObtainProfileUsesCurl(): void
    {
        $this->assertStringContainsString('curl_init', self::$sourceContents);
        $this->assertStringContainsString('curl_exec', self::$sourceContents);
        $this->assertStringContainsString('curl_close', self::$sourceContents);
        $this->assertStringContainsString('CURLOPT_RETURNTRANSFER', self::$sourceContents);
    }

    /**
     * Tests that the addressbook widget HTML references the AddressBook widget class.
     * The JavaScript must instantiate OffAmazonPayments.Widgets.AddressBook.
     */
    public function testAddressbookWidgetContainsCorrectJsClass(): void
    {
        $this->assertStringContainsString(
            'OffAmazonPayments.Widgets.AddressBook',
            self::$sourceContents
        );
    }

    /**
     * Tests that the addressbook widget references the addressBookWidgetDiv container.
     * The widget binds to this specific DOM element.
     */
    public function testAddressbookWidgetContainsDivId(): void
    {
        $this->assertStringContainsString('addressBookWidgetDiv', self::$sourceContents);
    }

    /**
     * Tests that the wallet widget HTML references the Wallet widget class.
     * The JavaScript must instantiate OffAmazonPayments.Widgets.Wallet.
     */
    public function testWalletWidgetContainsCorrectJsClass(): void
    {
        $this->assertStringContainsString(
            'OffAmazonPayments.Widgets.Wallet',
            self::$sourceContents
        );
    }

    /**
     * Tests that the wallet widget references the walletWidgetDiv container.
     * The widget binds to this specific DOM element.
     */
    public function testWalletWidgetContainsDivId(): void
    {
        $this->assertStringContainsString('walletWidgetDiv', self::$sourceContents);
    }

    /**
     * Tests that both widget functions use the AMAZON_SELLER_ID constant.
     * The seller ID is required for Amazon payment widget initialization.
     */
    public function testWidgetsReferenceSellerIdConstant(): void
    {
        $this->assertGreaterThanOrEqual(
            2,
            substr_count(self::$sourceContents, 'AMAZON_SELLER_ID'),
            'AMAZON_SELLER_ID should appear at least twice (once per widget)'
        );
    }

    /**
     * Tests that both widget functions include an onError callback.
     * Error handling is required for production Amazon payment widgets.
     */
    public function testWidgetsIncludeErrorHandling(): void
    {
        $this->assertGreaterThanOrEqual(
            2,
            substr_count(self::$sourceContents, 'onError'),
            'onError handler should appear at least twice (once per widget)'
        );
    }

    /**
     * Tests that the obtain profile function uses json_decode to parse responses.
     * Both the token info and profile API responses are JSON.
     */
    public function testObtainProfileUsesJsonDecode(): void
    {
        $this->assertStringContainsString('json_decode', self::$sourceContents);
    }

    /**
     * Tests that the obtain profile function sets the Authorization bearer header.
     * The user profile API requires a bearer token in the Authorization header.
     */
    public function testObtainProfileSetsBearerHeader(): void
    {
        $this->assertStringContainsString('Authorization: bearer', self::$sourceContents);
    }

    /**
     * Tests that the source file contains exactly three function definitions.
     * The file should define: amazon_obtain_profile, amazon_addressbook_widget, amazon_wallet_widget.
     */
    public function testSourceFileContainsExactlyThreeFunctions(): void
    {
        preg_match_all('/^\s*function\s+\w+\s*\(/m', self::$sourceContents, $matches);
        $this->assertCount(3, $matches[0], 'Expected exactly 3 function definitions in amazon.php');
    }

    /**
     * Tests that the widget functions reference the amazonOrderReferenceId variable.
     * This order reference is obtained from the Amazon Button widget and passed to both sub-widgets.
     */
    public function testWidgetsReferenceOrderReferenceId(): void
    {
        $this->assertStringContainsString('amazonOrderReferenceId', self::$sourceContents);
    }

    /**
     * Tests that the widget dimensions are set to the expected size.
     * Both widgets use 400x260 pixel dimensions.
     */
    public function testWidgetDimensions(): void
    {
        $this->assertGreaterThanOrEqual(
            2,
            substr_count(self::$sourceContents, '"400px"'),
            'Width 400px should appear at least twice'
        );
        $this->assertGreaterThanOrEqual(
            2,
            substr_count(self::$sourceContents, '"260px"'),
            'Height 260px should appear at least twice'
        );
    }
}
