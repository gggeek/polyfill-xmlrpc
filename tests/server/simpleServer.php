<?php
/**
 * NB: keep code in sync with the equivalent server in /demo
 */

require_once dirname(__DIR__) . '/../vendor/autoload.php';

use PhpXmlRpc\Polyfill\XmlRpc\XmlRpc as p;

function hello($method, $params, $userData)
{
    if (!count($params)) {
        return array('faultCode' => 1, 'faultString' => "missing first parameter");
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
   <examples/>
   <errors>
      <item>returns fault code 1 if the caller's name is not specified</item>
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

$server = p::xmlrpc_server_create();
p::xmlrpc_server_register_method($server, "introspection.hello", "hello");
p::xmlrpc_server_register_introspection_callback($server, "introspectionCallback");
$response = p::xmlrpc_server_call_method($server, $request, null);
p::xmlrpc_server_destroy($server);

if ($response) {
    // NB: unless we pass the 'encoding' => 'utf-8' output option to the server, there will be a Latin-1 declaration in the generated xml,
    //     in which case we should force the charset in the content-type header to avoid php overriding it and declaring UTF-8, as
    header('Content-Type: text/xml;charset=iso-8859-1');
    echo $response;
} else {
    // Q: is this possible at all?
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 500 Internal Server Error');
}
