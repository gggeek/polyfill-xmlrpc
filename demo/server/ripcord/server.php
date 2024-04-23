<?php
//require_once __DIR__ . "/../_prepend.php";

require_once(__DIR__ . '/vendor/autoload.php');

use Ripcord\Ripcord;

class DemoServer
{
    protected static $stateNames = array(
        "Alabama", "Alaska", "Arizona", "Arkansas", "California",
        "Colorado", "Columbia", "Connecticut", "Delaware", "Florida",
        "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas",
        "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan",
        "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada",
        "New Hampshire", "New Jersey", "New Mexico", "New York", "North Carolina",
        "North Dakota", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island",
        "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont",
        "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming",
    );

    public function examples_getStateName($stateNo)
    {
        if (isset(self::$stateNames[$stateNo])) {
            return self::$stateNames[$stateNo];
        }

        return array('faultCode' => 800, 'faultString' => "I don't have a state for the index '$stateNo'");
    }
}

$server = new DemoServer();
// work around a bug in current Server class when no documentor is passed in
$documentor = new \Ripcord\Documentator\Documentor(null, new \Ripcord\Documentator\Parsers\PhpDoc());
$ripcordServer = Ripcord::server($server, null, $documentor);
$ripcordServer->run();
