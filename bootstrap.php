<?php

/**
 * @author Gaetano Giunta
 * @copyright (c) 2020 G. Giunta
 * @license code licensed under the BSD License: see license.txt
 */

use PhpXmlRpc\Polyfill\XmlRpc as p;

if (!function_exists('xmlrpc_decode')) {
    function () { return p\XmlRpc::xmlrpc_decode(); }
}

if (!function_exists('xmlrpc_decode_request')) {
    function () { return p\XmlRpc::xmlrpc_decode_request(); }
}

if (!function_exists('xmlrpc_encode')) {
    function () { return p\XmlRpc::xmlrpc_encode(); }
}

if (!function_exists('xmlrpc_encode_request')) {
    function () { return p\XmlRpc::xmlrpc_encode_request(); }
}

if (!function_exists('xmlrpc_get_type')) {
    function () { return p\XmlRpc::xmlrpc_get_type(); }
}

if (!function_exists('xmlrpc_set_type')) {
    function () { return p\XmlRpc::xmlrpc_set_type(); }
}

if (!function_exists('xmlrpc_is_fault')) {
    function () { return p\XmlRpc::xmlrpc_is_fault(); }
}

if (!function_exists('xmlrpc_server_create')) {
    function () { return p\XmlRpc::xmlrpc_server_create(); }
}

if (!function_exists('xmlrpc_server_destroy')) {
    function () { return p\XmlRpc::xmlrpc_server_destroy(); }
}

if (!function_exists('xmlrpc_server_register_method')) {
    function () { return p\XmlRpc::xmlrpc_server_register_method(); }
}

if (!function_exists('xmlrpc_server_call_method')) {
    function () { return p\XmlRpc::xmlrpc_server_call_method(); }
}

if (!function_exists('xmlrpc_parse_method_descriptions')) {
    function () { return p\XmlRpc::xmlrpc_parse_method_descriptions(); }
}

if (!function_exists('xmlrpc_server_add_introspection_data')) {
    function () { return p\XmlRpc::xmlrpc_server_add_introspection_data(); }
}

if (!function_exists('xmlrpc_server_register_introspection_callback')) {
    function () { return p\XmlRpc::xmlrpc_server_register_introspection_callback(); }
}
