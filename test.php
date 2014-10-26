<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

use cebe\pulse\discover\Packet;

require(__DIR__ . '/vendor/autoload.php');

$msg = '�y�9 6	mmMA�`���.g�Tl�/�,V:\9��tU�';


var_dump(Packet::createFromString($msg));
