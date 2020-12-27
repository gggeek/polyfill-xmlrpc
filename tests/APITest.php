<?php
/**
 * @todo test xmlrpc_encode_request(NULL, array())
 *
 * @author Gaetano Giunta
 * @copyright (c) 2020 G. Giunta
 * @license code licensed under the BSD License: see license.txt
 */

include_once __DIR__ . '/PolyfillTestCase.php';

use PhpXmlRpc\PhpXmlRpc;
use PhpXmlRpc\Polyfill\XmlRpc\XmlRpc as p;

class ApiTest extends PolyfillTestCase
{
    /**
     * @dataProvider getSafeGetTypeValues
     */
    function testGetType($val)
    {
        $ok = xmlrpc_get_type($val);
        $ok1 = p::xmlrpc_get_type($val);
        $this->assertEquals($ok, $ok1, "xmlrpc_get_type failed for ".var_export($val, true));
    }

    /**
     * @dataProvider getFaultValues
     */
    function testIsFault($val)
    {
        $ok = xmlrpc_is_fault($val);
        $ok1 = p::xmlrpc_is_fault($val);
        $this->assertEquals($ok, $ok1, "xmlrpc_is_fault failed for ".var_export($val, true));
    }

    /**
     * @dataProvider getSafeEncodingValues
     */
    function testEncoding($val)
    {
        $defaultPrecision = PhpXmlRpc::$xmlpc_double_precision;
        PhpXmlRpc::$xmlpc_double_precision = 6;
        $defaultEncoding = PhpXmlRpc::$xmlrpc_internalencoding;
        PhpXmlRpc::$xmlrpc_internalencoding = 'ISO-8859-1';

        $ko = $this->normalizeXmlFormatting(xmlrpc_encode($val));
        $ko1 = $this->normalizeXmlFormatting(p::xmlrpc_encode($val));
        $this->assertEquals($ko, $ko1, "xmlrpc_encode failed for ".var_export($val, true));

        $ok = xmlrpc_decode($ko);
        $ok1 = p::xmlrpc_decode($ko1);
        $this->assertEquals($ok, $ok1, "xmlrpc_decode failed for ".var_export($val, true));

        $ok = $this->normalizeXmlFormatting(xmlrpc_encode_request('hello', $val));
        $ok1 = $this->normalizeXmlFormatting(p::xmlrpc_encode_request('hello', $val));
        $this->assertEquals($ok, $ok1, "xmlrpc_encode_request failed for ".var_export($val, true));

        $methodName = '';
        $ko = xmlrpc_decode_request($ok, $methodName);
        $ko1 = p::xmlrpc_decode_request($ok1, $methodName);
        $this->assertEquals($ko, $ko1, "xmlrpc_decode_request failed for ".var_export($ok, true));

        //$ko = xmlrpc_decode_request('zzz'.$ok, $methodname);
        //echo  'DECODED BAD  : '; var_dump($ko);

        // methodresponse generated
        $ok = $this->normalizeXmlFormatting(xmlrpc_encode_request(null, $val));
        $ok1 = $this->normalizeXmlFormatting(p::xmlrpc_encode_request(null, $val));
        $this->assertEquals($ok, $ok1, "xmlrpc_encode_request failed for ".var_export($val, true));

        $methodName = '***';
        $methodName1 = '***';
        $ko = $this->normalizeXmlFormatting(xmlrpc_decode_request($ok, $methodName));
        $ko1 = $this->normalizeXmlFormatting(xmlrpc_decode_request($ok1, $methodName1));
        $this->assertEquals($ko, $ko1, "xmlrpc_decode_request failed for ".var_export($val, true));

        PhpXmlRpc::$xmlrpc_internalencoding = $defaultEncoding;
        PhpXmlRpc::$xmlpc_double_precision = $defaultPrecision;
        //@fclose($v3);
    }

    protected function normalizeXmlFormatting($text)
    {
        return preg_replace(
            array('/^<\\?xml +version="1\\.0" +encoding="([^"]*)" \\?/', '#<params></params>#', '#<string></string>#', '#<data></data>#', '/^ +/m', '/\\n/s'),
            array('<?xml version="1.0" encoding="$1"?', '<params/>', '<string/>', '<data/>', '', ''),
            $text);
    }

    public function getSafeEncodingValues()
    {
        $vals = $this->getScalarValues();

        $v1 = '20060707T12:00:00';
        p::xmlrpc_set_type($v1, 'datetime');
        $v2 = 'hello world';
        p::xmlrpc_set_type($v2, 'base64');
        $vals[] = array($v1);
        $vals[] = array($v2);
        $vals[] = array(array('hello' => true, 'hello', 'world')); // mixed - encode KO (2 members with null name) but decode will be fine!!!
        $vals[] = array(array('methodname' => 'hello', 'params' => array())); // struct

        $vals = array_merge($vals, $this->getFaultValues());

        return $vals;
    }

    public function getSafeGetTypeValues()
    {
        return $this->getScalarValues();
    }

    /// @todo add more cases with wrong type for faultCode & faultString: null, float, object, resource
    public function getFaultValues()
    {
        $vals = array();

        $vals[] = array(array());
        $vals[] = array(array('faultCode' => 666));
        $vals[] = array(array('faultString' => 'hello world'));
        $vals[] = array(array('faultCode' => 666, 'faultString' => 'hello world'));
        $vals[] = array(array('faultCode' => 'hello world'));
        $vals[] = array(array('faultString' => 666));
        $vals[] = array(array('faultCode' => 'hello world', 'faultString' => 666));
        $vals[] = array(array('faultCode' => 666, 'faultString' => 'hello world', 'faultWhat?' => 'dunno'));
        $vals[] = array(array('faultCode' => array(666), 'faultString' => 'hello world'));
        $vals[] = array(array('faultCode' => 666, 'faultString' => array('hello world')));

        return $vals;
    }

    /**
     * A set of values used in most tests
     * @todo add more values: Object, DateTime, function, UTF8 text, Latin1 text, more nested arrays...
     */
    protected function getScalarValues()
    {
        $vals = array(
            array(true),
            array(false),
            array(0),
            array(1),
            array(-1),
            array(1.0), // fails encoding tests
            array(1.1), // fails encoding tests
            array(-1.1), // fails encoding tests
            array(1.123456789),
            array(-1.123456789),
            array(null), // base 64 type???, encoded as empty string
            array(''),
            array('1'),
            array('-1'),
            array(' 1 '),
            array('1.1'),
            array(' 1.1 '),
            array('20060101T12:00:00'),
            array('a.b.c.å.ä.ö.€.'), /// @todo replace with latin-1 stuff
            array('Τὴ γλῶσσα μοῦ ἔδωσαν ἑλληνικὴ'), /// @todo replace with latin-1 stuff
            array(base64_encode('hello')), // string
            array(fopen(__FILE__, 'r')),
            array(array()),
            array(array('a')),
            array(array(array(1))),
            array(array('2' => true, false)), // array - when decoded array keys will be reset
            array(array('hello' => 'world')), // struct
            array(array('hello' => true, 'world')), // mixed
            //new apitests() // CRASH!!!,
        );

        return $vals;
    }
}
