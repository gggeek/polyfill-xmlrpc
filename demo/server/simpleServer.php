<?php
/**
 * A simple demo of the xmlrpc extension API - server side.
 * NB: keep code in sync with the equivalent server in /tests/server
 */

require_once __DIR__ . '/_prepend.php';

function hello($method, $params, $userData)
{
    if (!count($params)) {
        return array('faultCode' => 1, 'faultString' => "missing parameter");
    }

    if (!is_string($params[0])) {
        return array('faultCode' => 2, 'faultString' => "parameter is not a string: " . xmlrpc_get_type($params[0]));
    }

    return "hello {$params[0]}";
}

function introspectionCallback($userData)
{
   return <<< END
<?xml version='1.0'?>
<introspection version='1.0'>
 <methodList>
  <methodDescription name='introspection.hello'>
   <!--<author>Dan Libby</author>-->
   <purpose>greets the caller and demonstrates use of introspection mechanism</purpose>
   <signatures>
    <signature>
     <params>
      <value type='string' name='name'>name of the caller</value>
     </params>
     <returns>
      <value type='string'>a greeting to the caller</value>
     </returns>
    </signature>
   </signatures>
   <!--<see><item>system.listMethods</item></see>-->
   <!--<examples/>-->
   <errors>
      <item>returns fault code 1 if the caller's name is not specified</item>
      <item>returns fault code 2 if the caller's name is not a string</item>
   </errors>
   <!--<notes>
    <item>this is a lame example</item>
    <item>example of multiple notes</item>
   </notes>
   <bugs/>
   <todo/>-->
  </methodDescription>
 </methodList>
</introspection>
END;
}

$request = file_get_contents('php://input');

$server = xmlrpc_server_create();
xmlrpc_server_register_method($server, "introspection.hello", "hello");
xmlrpc_server_register_introspection_callback($server, "introspectionCallback");
$response = xmlrpc_server_call_method($server, $request, null);
xmlrpc_server_destroy($server);

if ($response) {
    // NB: unless we pass the 'encoding' => 'utf-8' output option to the server, there will be a Latin-1 declaration in the generated xml,
    //     in which case we should force the charset in the content-type header to avoid php overriding it and declaring UTF-8
    header('Content-Type: text/xml;charset=iso-8859-1');
    echo $response;
} else {
    // Looking at sources, it seems that the only possible case for this is if there was an oom error.
    // We should probably try to echo back a valid xmlrpc fault response while avoiding _any_ memory allocation...
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 500 Internal Server Error');
}

require_once __DIR__ . "/_append.php";
