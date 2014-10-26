<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

use cebe\pulse\discover\Packet;

require(__DIR__ . '/vendor/autoload.php');

$msg = '���9';

echo strlen($msg);
echo $h = substr(bin2hex($msg), 4) . "\n";
echo hexdec($h) . "\n";
echo long2ip(hexdec($h)) . "\n";

//efbf bdef bfbd efbf bd39