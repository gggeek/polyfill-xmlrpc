<?php

namespace PhpXmlRpc\Polyfill\XmlRpc;

use PhpXmlRpc\Response;
use PhpXmlRpc\Server as BaseServer;
use PhpXmlRpc\Value;

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
     * @param array $desc
     * @return int 1 if anything got added to the docs, 0 otherwise
     * @see XMLRPC_ServerAddIntrospectionData in xmlrpc_intropspection.c
     * @todo save as well in a new member of $this->dmap the combined methodList+typeList, so that it can be used by _xmlrpcs_describeMethods
     */
    public function add_introspection_data($desc)
    {
        $out = 0;
        //if (is_array($desc) && isset($desc['typeList']) && is_array($desc['typeList'])) {
        //}
        if (is_array($desc) && isset($desc['methodList']) && is_array($desc['methodList'])) {
            foreach($desc['methodList'] as $methodDesc) {
                if (!isset($methodDesc['name']) || !isset($this->dmap[$methodDesc['name']])) {
                    continue;
                }
                $methodName = $methodDesc['name'];
                if (isset($methodDesc['purpose'])) {
                    $this->dmap[$methodName]['docstring'] = $methodDesc['purpose'];
                    $out = 1;
                }
                if (!isset($methodDesc['signatures']) || !is_array($methodDesc['signatures'])) {
                    continue;
                }
                /// @todo avoid clearing existing sigs unless there is at least one valid sig provided
                $this->dmap[$methodName]['signature'] = array();
                foreach($methodDesc['signatures'] as $methodSig) {
                    if (is_array($methodSig) && isset($methodSig['params']) && isset($methodSig['returns']) &&
                        is_array($methodSig['params']) && is_array($methodSig['returns']) && count($methodSig['returns'])) {
                        /// @todo decode the found types if they are in the typeList or unknown
                        // First param is return type
                        $params = array($methodSig['returns'][0]['type']);
                        $paramDescriptions = array(isset($methodSig['returns'][0]['description']) ? $methodSig['returns'][0]['description'] : '');
                        foreach($methodSig['params'] as $param) {
                            if (isset($param['optional']) && $param['optional']) {
                                // Save sig found so far, since this param is optional
                                /// @bug we should only do this if following parameters are optional too...
                                // use an array key which forces uniqueness
                                $this->dmap[$methodName]['signature'][implode('/', $params)] = $params;
                                $this->dmap[$methodName]['signature_docs'][implode('/', $params)] = $paramDescriptions;
                            }
                            $params[] = $param['type'];
                            $paramDescriptions[] = isset($param['description']) ? $param['description'] : '';
                        }
                        $this->dmap[$methodName]['signature'][implode('/', $params)] = $params;
                        $this->dmap[$methodName]['signature_docs'][implode('/', $params)] = $paramDescriptions;
                        $out = 1;
                    }
                }
                $this->dmap[$methodName]['signature'] = array_values($this->dmap[$methodName]['signature']);
                $this->dmap[$methodName]['signature_docs'] = array_values($this->dmap[$methodName]['signature_docs']);
            }
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
     * Reimplement to allow users to register their own 'system.' methods
     * @param string $methName
     * @return bool
     */
    protected function isSyscall($methName)
    {
        return in_array($methName, array(
            'system.listMethods', 'system.methodHelp', 'system.methodSignature', 'system.multicall', 'system.getCapabilities', 'system.describeMethods',
        ));
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

        $dmap['system.describeMethods'] = array(
            'function' => 'PhpXmlRpc\Polyfill\XmlRpc\Server::_xmlrpcs_describeMethods',
            'signature' => array(
                array(Value::$xmlrpcStruct),
                array(Value::$xmlrpcStruct, Value::$xmlrpcString),
                array(Value::$xmlrpcStruct, Value::$xmlrpcArray)
            ),
            'docstring' => 'See http://xmlrpc-epi.sourceforge.net/specs/rfc.system.describeMethods.php',
            'signature_docs' => array(
                array('list of method descriptions: struct{}'),
                array('list of method descriptions: struct{}', 'Method to describe: string'),
                array('list of method descriptions: struct{}', 'List of methods to describe: string[]')
            ),
        );

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
     * @return Response
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

    /**
     * Note: we _could_ improve this, however the xmlrpc extension has apparently a bug, which prevents it from listing
     * _any_ registered method when answering to these calls. So it's just not worth it...
     * @param Server $server
     * @param \PhpXmlRpc\Request|string|string[]|null $req
     * @return Response
     * @todo finish implementation - esp. the missing TypeList
     */
    public static function _xmlrpcs_describeMethods($server, $req = null)
    {
        // allow jit evaluation of xml-based method descriptions
        if ($server->introspectionCallback) {
            $server->add_introspection_data(Server::parse_method_descriptions(call_user_func($server->introspectionCallback)));
            $server->register_introspection_callback(null);
        }

        if (is_object($req)) {
            if ($req->getNumParams() > 0) {
                $methods = array();
                $p1 = $req->getParam(0);
                if ($p1->scalartyp() == Value::$xmlrpcString) {
                    $methods[] = $p1->scalarval();
                } else {
                    foreach($p1 as $val) {
                        /// @todo check that $val is scalar...
                        $methods[] = $val->scalarval();
                    }
                }
            } else {
                $methods = null;
            }
        } else {
            $methods = is_string($req) ? array($req): $req;
        }

        $toDescribe = array();
        foreach ($server->dmap as $key => $val) {
            if ($methods === null || in_array($key, $methods)) {
                $toDescribe[$key] = $val;
            }
        }
        foreach ($server->getSystemDispatchMap() as $key => $val) {
            if ($methods === null || in_array($key, $methods)) {
                $toDescribe[$key] = $val;
            }
        }

        $mList = array();
        foreach ($toDescribe as $method => $mData) {
            $sigs = array();
            foreach($mData['signature'] as $i => $signature) {
                $pars = array();
                foreach(array_slice($signature, 1) as $param) {
                    $pars[] = new Value(array(
                            'type' => new Value($param, Value::$xmlrpcString),
                            'description' => new Value('', Value::$xmlrpcString),
                            //'name' => '',
                            'optional' => new Value(false, Value::$xmlrpcBoolean),
                        ), Value::$xmlrpcStruct);
                }
                $ret = new Value(array(
                        'type' => new Value($signature[0], Value::$xmlrpcString),
                        'description' => new Value('', Value::$xmlrpcString),
                        //'name' => '',
                        'optional' => new Value(false, Value::$xmlrpcBoolean),
                    ), Value::$xmlrpcStruct);
                $sigs[] = new Value(array(
                        'params' => new Value($pars, Value::$xmlrpcArray),
                        'returns' => new Value(array($ret), Value::$xmlrpcArray)
                    ), Value::$xmlrpcStruct);
            }
            $mDesc = array(
                'name' => new Value($method, Value::$xmlrpcString),
                //'version' => '',
                //'author' => '',
                'purpose' => new Value($mData['docstring'], Value::$xmlrpcString),
                'signatures' => new Value($sigs, Value::$xmlrpcArray),
                //'bugs' => array(),
                //'errors' => array(),
                //'examples' => array(),
                //'history' => array(),
                //'notes' => array(),
                //'see' => array(),
                //'todo' => array(),
            );
            $mList[] = new Value($mDesc, Value::$xmlrpcStruct);
        }

        return new Response(new Value(array('methodList' => new Value($mList, Value::$xmlrpcArray)), Value::$xmlrpcStruct));
    }
}
