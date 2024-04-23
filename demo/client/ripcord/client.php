<?php
//require_once __DIR__ . "/../_prepend.php";

// Ex:
//define('XMLRPCSERVER', 'http://127.0.0.1/demo/server/ripcord/server.php');

require_once(__DIR__ . '/vendor/autoload.php');

use Ripcord\Ripcord;
use \Ripcord\Client\Transport;

// we prefer the Curl transport as it seems to work better oob when f.e. sending requests to 127.0.0.1
$client = Ripcord::xmlrpcClient(XMLRPCSERVER, array(), new Transport\Curl());

$stateNo = rand(1, 50);

$stateName = $client->examples_getStateName($stateNo);

if (is_array($stateName)) {
    echo "Server returned error: " . htmlspecialchars($stateName['faultString']);
} else {
    echo "State nr. $stateNo is: " . htmlspecialchars($stateName);
}
