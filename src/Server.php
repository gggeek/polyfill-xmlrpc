<?php

namespace PhpXmlRpc\Polyfill\XmlRpc;

use PhpXmlRpc\Server as BaseServer;

class Server extends BaseServer
{
    protected $introspectionCallback = null;

    /**
     * @param $function
     * @return bool
     */
    public function register_introspection_callback($function)
    {
        $this->introspectionCallback = $function;
        /// @todo when should we return false ?
        return true;
    }

    /**
     * @param $desc
     * @return int 1 if anything got added to the docs, 0 otherwise
     * @see XMLRPC_ServerAddIntrospectionData in xmlrpc_intropspection.c
     * @todo implement
     */
    public function add_introspection_data($desc)
    {
        $out = 0;
        if (is_array($desc) && isset($desc['methodList']) && is_array($desc['methodList'])) {
            foreach($desc['methodList'] as $methodDesc) {
                //if (isset($this->dmap[$methodName])) {
                //    $this->dmap[$methodName]['docstring'] = $methodDesc;
                //    $out = 1;
                //}
            }
        }
        if (is_array($desc) && isset($desc['typeList']) && is_array($desc['typeList'])) {
        }
        return $out;
    }

    /**
     * @param string $xml
     * @return array
     * @todo implement
     * @see http://xmlrpc-epi.sourceforge.net/specs/rfc.system.describeMethods.php
     */
    public static function parse_method_descriptions($xml)
    {
        return array();
    }

    /**
     * @return array[]
     */
    public function getSystemDispatchMap()
    {
        $dmap = parent::getSystemDispatchMap();
        if (isset($dmap['system.methodHelp'])) {
            $dmap['system.methodHelp']['function'] = 'PhpXmlRpc\Polyfill\XmlRpc\Server::_xmlrpcs_methodHelp';
        }
        if (isset($dmap['system.methodSignature'])) {
            $dmap['system.methodSignature']['function'] = 'PhpXmlRpc\Polyfill\XmlRpc\Server::_xmlrpcs_methodSignature';
        }
        return $dmap;
    }

    /**
     * @param Server $server
     * @param \PhpXmlRpc\Request $req
     * @return \PhpXmlRpc\Response
     */
    public static function _xmlrpcs_methodHelp($server, $req)
    {
        // allow jit evaluation of xml-based method descriptions
        if ($server->introspectionCallback) {
            $server->add_introspection_data(Server::parse_method_descriptions(call_user_func($server->introspectionCallback)));
            $server->register_introspection_callback(null);
        }
        return parent::_xmlrpcs_methodHelp($server, $req);
    }

    /**
     * @param Server $server
     * @param \PhpXmlRpc\Request $req
     * @return \PhpXmlRpc\Response
     */
    public static function _xmlrpcs_methodSignature($server, $req)
    {
        // allow jit evaluation of xml-based method descriptions
        if ($server->introspectionCallback) {
            $server->add_introspection_data(Server::parse_method_descriptions(call_user_func($server->introspectionCallback)));
            $server->register_introspection_callback(null);
        }
        return parent::_xmlrpcs_methodSignature($server, $req);
    }
}
