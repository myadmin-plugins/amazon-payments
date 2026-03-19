<?php

/**
 * PHPUnit bootstrap file for myadmin-amazon-payments tests.
 *
 * Defines fallback functions that may not be available in the test
 * environment (e.g., gettext's _() function).
 */

// The _() function is a PHP built-in alias for gettext().
// If the gettext extension is not loaded, define a passthrough fallback
// so that Plugin::getSettings() can be tested without it.
if (!function_exists('_')) {
    function _($message)
    {
        return $message;
    }
}

// Load the Composer autoloader. When running from a standalone checkout, use
// the package's own vendor/autoload.php. When running as part of a parent
// project, walk up to find the project root autoloader.
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];
foreach ($autoloadPaths as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        break;
    }
}
