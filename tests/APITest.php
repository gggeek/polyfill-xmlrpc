<?php
/**
 * @todo test xmlrpc_encode_request(NULL, array())
 *
 * @author Gaetano Giunta
 * @copyright (c) 2020 G. Giunta
 * @license code licensed under the BSD License: see license.txt
 */

include_once __DIR__ . '/PolyfillTestCase.php';

use PhpXmlRpc\Polyfill\XmlRpc\XmlRpc as p;

class ApiTest extends PolyfillTestCase
{
    /**
     * @dataProvider getSafeEncodingValues
     */
    function testEncoding($val)
    {
        //echo "<table>\n<thead><tr><th>ORIGINAL VALUE</th><th>XMLRPC TYPE</th><th>ALT TYPE</th><th>XMLRPC ENCODED</th><th>ALT ENCODED</th><th>DECODED VAL</th><th>ALT DECODED</th><th>XMLRPC REQUEST</th><th>ALT REQ</th><th>DECODED REQ</th><th>ALT DEC REQ</th><th>XMLRPC RESP</th><th>ALT RESP</th><th>DECODED RESP</th><th>ALT DEC RESP</th></tr></thead>\n";
        //echo '<tr><td>';
        var_dump($val);

        //echo '</td><td>';
        $ok = xmlrpc_get_type($val);
        //echo $ok;
        //echo '</td><td>';
        $ok1 = p::xmlrpc_get_type($val);
        //if ($ok !== $ok1) echo '<b>' . $ok1 . "</b>\n";
        //echo '</td><td>';
        $this->assertEquals($ok, $ok1, "xmlrpc_get_type failed for ".var_export($val, true));

        $ko = xmlrpc_encode($val);
        //echo htmlspecialchars($ko);
        //echo '</td><td>';
        $ko1 = p::xmlrpc_encode($val);
        //if (preg_replace(array('/ /', "/\n/", "/\r/", '/encoding="[^"]+"/', '!<data/>!', '!<string/>!', '!<params/>!'), array('', '', '', '', '<data></data>', '<string></string>', '<params></params>'), $ko) != str_replace(array(' ', "\n", "\r"), array('', '', ''), $ko1)) {
        //    echo '<b>' . htmlspecialchars($ko1) . "</b>\n";
        //}
        //echo '</td><td>';
        $this->assertEquals($ko, $ko1, "xmlrpc_encode failed for ".var_export($val, true));

        $ok = xmlrpc_decode($ko);
        //var_dump($ok);
        //echo '</td><td>';
        $ok1 = p::xmlrpc_decode($ko1);
        //if ($ok !== $ok1) {
        //    echo '<b>';
        //    var_dump($ok1);
        //    echo "</b>";
        //}
        //echo '</td><td>';
        $this->assertEquals($ok, $ok1, "xmlrpc_decode failed for ".var_export($val, true));

        $ok = xmlrpc_encode_request('hello', $val);
        //echo htmlspecialchars($ok);
        //echo '</td><td>';
        $ok1 = p::xmlrpc_encode_request('hello', $val);
        //if (preg_replace(array('/ /', "/\n/", "/\r/", '/encoding="[^"]+"/', '!<data/>!', '!<string/>!', '!<params/>!'), array('', '', '', '', '<data></data>', '<string></string>', '<params></params>'), $ok) != str_replace(array(' ', "\n", "\r"), array('', '', ''), $ok1)) {
        //    echo '<b>' . htmlspecialchars($ok1) . "</b>\n";
        //}
        //echo '</td><td>';
        $this->assertEquals($ok, $ok1, "xmlrpc_encode_request failed for ".var_export($val, true));

        $methodname = '';
        $ko = xmlrpc_decode_request($ok, $methodname);
        var_dump($ko);
        //echo '</td><td>';
        $ko1 = p::xmlrpc_decode_request($ok1, $methodname);
        //if ($ko !== $ko1) {
        //    echo '<b>';
        //    var_dump($ko1);
        //    echo "</b>";
        //}
        //echo '</td><td>';
        $this->assertEquals($ko, $ko1, "xmlrpc_decode_request failed for ".var_export($val, true));

        //$ko = xmlrpc_decode_request('zzz'.$ok, $methodname);
        //echo  'DECODED BAD  : '; var_dump($ko);

        $ok = xmlrpc_encode_request(null, $val); // methodresponse generated
        //echo htmlspecialchars($ok);
        //echo '</td><td>';
        $ok1 = p::xmlrpc_encode_request(null, $val);
        //if (preg_replace(array('/ /', "/\n/", "/\r/", '/encoding="[^"]+"/', '!<data/>!', '!<string/>!', '!<params/>!'), array('', '', '', '', '<data></data>', '<string></string>', '<params></params>'), $ok) != str_replace(array(' ', "\n", "\r"), array('', '', ''), $ok1)) {
        //    echo '<b>' . htmlspecialchars($ok1) . "</b>\n";
        //}
        //echo '</td><td>';
        $this->assertEquals($ok, $ok1, "xmlrpc_encode_request failed for ".var_export($val, true));

        $methodname = '***';
        $methodname1 = '***';
        $ko = xmlrpc_decode_request($ok, $methodname);
        //var_dump($ko);
        //echo '</td><td>';
        $ko1 = xmlrpc_decode_request($ok1, $methodname1);
        //if ($ko !== $ko1) {
        //    echo '<b>';
        //    var_dump($ko1);
        //    echo "</b>";
        //}
        //echo "</td></tr>\n";
        $this->assertEquals($ko, $ko1, "xmlrpc_decode_request failed for ".var_export($val, true));

        //@fclose($v3);
    }

    /**
     * @todo add encoding of Object, DateTime, function, UTF8 text, Latin1 text, ...
     */
    public function getSafeEncodingValues()
    {
        $v1 = '20060707T12:00:00';
        p::xmlrpc_set_type($v1, 'datetime');
        $v2 = 'hello world';
        p::xmlrpc_set_type($v2, 'base64');
        $v3 = fopen(__FILE__, 'r');
        $vals = array(
            array(true),
            array(false),
            array(0),
            array(1),
            array(1.0),
            array(1.1),
            array(1.123456789),
            array(null), // base 64 type???, encoded as empty string
            array(''),
            array('1'),
            array('20060101T12:00:00'),
            array($v1),
            array(base64_encode('hello')), // string
            array($v2),
            array($v3),
            array(array()),
            array(array('a')),
            array(array(array(1))),
            array(array('2' => true, false)), // array - when decoded array keys will be reset
            array(array('hello' => 'world')), // struct
            array(array('hello' => true, 'world')), // mixed
            array(array('hello' => true, 'hello', 'world')), // mixed - encode KO (2 members with null name) but decode will be fine!!!
            array(array('methodname' => 'hello', 'params' => array())), // struct
            array(array('faultCode' => 666, 'faultString' => 'hello world')),
            array(array('faultCode' => 666, 'faultString' => 'hello world', 'faultWhat?' => 'dunno')),
            array(array('faultCode' => 666, 'faultString' => array('hello world'))),
            //new apitests() // CRASH!!!,
        );

        return $vals;
    }
}
