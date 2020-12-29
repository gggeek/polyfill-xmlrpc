<?php
/*
 * An xmlrpc server implementing a suite of services used for interoperability testing.
 * Also used as part of the test suite of this library.
 */

/// @todo implement the full suite of validator1 and interopEchoTests

require_once __DIR__ . "/_prepend.php";

use PhpXmlRpc\Polyfill\XmlRpc\XmlRpc as p;

$methods = array(

);
$signatures = array(

);

$request = file_get_contents('php://input');

// NB: unless we pass the 'encoding' => 'utf-8' output option to the server, there will be a Latin-1 declaration in the generated xml,
//     in which case we should force the charset in the content-type header to avoid php overriding it and declaring UTF-8, as
$output_options = array();
$ct_charset= ';charset=iso-8859-1';

// Allow the caller to switch between usage of the native extension (non-emulated) and the emulated implementation
if (isset($_GET['FORCE_POLYFILL']) && $_GET['FORCE_POLYFILL']) {
    $server = p::xmlrpc_server_create();
    foreach ($methods as $methodName => $function) {
        p::xmlrpc_server_register_method($server, $methodName, $function);
    }
    p::xmlrpc_server_add_introspection_data($server, $signatures);
    $response = p::xmlrpc_server_call_method($server, $request, null, $output_options);
    p::xmlrpc_server_destroy($server);
} else {
    if (!isset($_GET['NO_POLYFILL']) || !$_GET['NO_POLYFILL']) {
        require_once dirname(__DIR__) . '/../bootstrap.php';
    }
    $server = xmlrpc_server_create();
    foreach ($methods as $methodName => $function) {
        xmlrpc_server_register_method($server, $methodName, $function);
    }
    xmlrpc_server_add_introspection_data($server, $signatures);
    $response = xmlrpc_server_call_method($server, $request, null);
    xmlrpc_server_destroy($server);
}

if ($response) {
    header('Content-Type: text/xml'.$ct_charset);
    echo $response;
} else {
    // Q: is this possible at all?
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 500 Internal Server Error');
}

require_once __DIR__ . "/_append.php";
