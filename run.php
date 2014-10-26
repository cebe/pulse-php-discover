<?php

use cebe\pulse\discover\Address;
use cebe\pulse\discover\Device;
use cebe\pulse\discover\DiscoveryManager;
use cebe\pulse\discover\Packet;

require(__DIR__ . '/vendor/autoload.php');

$loop = React\EventLoop\Factory::create();

// setup

$discoveryManager = new DiscoveryManager();
$discoveryManager->start($loop);



// TCP Server

$socket = new React\Socket\Server($loop);
$socket->on('connection', function ($conn) {
//    $conn->write("Hello there!\n");
//    $conn->write("Welcome to this amazing server!\n");
//    $conn->write("Here's a tip: don't say anything.\n");
	echo "connection from " . $conn->getRemoteAddress() . "\n";
    $conn->on('data', function ($data) use ($conn) {
	    echo "$data\n";
        $conn->close();
    });
});
$socket->listen(1338);


$loop->run();


