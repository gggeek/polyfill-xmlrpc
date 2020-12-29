<?php
/**
 * Hackish code used to make the demos both viewable as source, runnable, and viewable as html
 */

// Make errors visible
ini_set('display_errors', true);
error_reporting(E_ALL);

if (isset($_GET['showSource']) && $_GET['showSource']) {
    $file = debug_backtrace()[0]['file'];
    highlight_file($file);
    die();
}

/// @todo make sure that we are not loading bootstrap.php when $_GET['NO_POLYFILL'] is set...
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    include_once(__DIR__ . '/../../vendor/autoload.php');
} else {
    die('Dependencies need to be installed via Composer in order to run the demo');
}

// Out-of-band information: let the client manipulate the server operations.
// We do this to help the testsuite script: do not reproduce in production!
if (isset($_COOKIE['PHPUNIT_SELENIUM_TEST_ID']) && extension_loaded('xdebug')) {
    $GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] = '/tmp/phpxmlrpc_coverage';
    if (!is_dir($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'])) {
        mkdir($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY']);
        chmod($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'], 0777);
    }

    include_once __DIR__ . "/../../vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/prepend.php";
}
