<?php
/**
 * Hackish code used to make the demos able to be executed, viewed as source, and used for code coverage tests
 * Do not reproduce in production!
 */

// Make errors visible
ini_set('display_errors', true);
error_reporting(E_ALL);

// Hackish code used to make the demos both viewable as source and runnable
if (isset($_GET['showSource']) && $_GET['showSource']) {
    $file = debug_backtrace()[0]['file'];
    highlight_file($file);
    die();
}

/* ATM autoloading is handled by the single clients
/// @todo make sure that we are not loading bootstrap.php when $_GET['NO_POLYFILL'] is set:
///       use phpxmlrpc's custom autoloader plus include the classes of this lib
//if (isset($_GET['NO_POLYFILL']) && $_GET['NO_POLYFILL']) {

//} else {
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        include_once(__DIR__ . '/../../vendor/autoload.php');
    } else {
        die('Dependencies need to be installed via Composer in order to run the demo');
    }
//}
*/

/// @todo Let unit tests run against localhost, 'plain' demos against a known public server
if (isset($_SERVER['HTTPSERVER'])) {
    define('XMLRPCSERVER', 'http://'.$_SERVER['HTTPSERVER'].'/demo/server/server.php');
} else {
    define('XMLRPCSERVER', 'http://tanoconsulting.com/sw/xmlrpc/demo/server/server.php');
    //define('XMLRPCSERVER', 'http://127.0.0.1/demo/server/ripcord/server.php');
}
