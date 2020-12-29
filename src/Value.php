<?php


namespace PhpXmlRpc\Polyfill\XmlRpc;

use PhpXmlrpc\Value as BaseValue;

class Value extends BaseValue
{
    /// @todo make these two memebers virtual via __get and possibly __set
    public $scalar;
    public $xmlrpc_type;
}
