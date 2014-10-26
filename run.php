<?php

use cebe\pulse\discover\Address;
use cebe\pulse\discover\Device;
use cebe\pulse\discover\DiscoveryManager;
use cebe\pulse\discover\Packet;

require(__DIR__ . '/vendor/autoload.php');

$loop = React\EventLoop\Factory::create();

// config

// generates a random node ID, should be replaced with a real hash of the TLS cert
$id = rtrim(\Base32\Base32::encode(hash('sha256', rand() . microtime(true), true)), '=');
$servicePort = rand(1337, 32000);

// setup

$discoveryManager = new DiscoveryManager($id);
$discoveryManager->servicePort = $servicePort;
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
$socket->listen($servicePort);


$loop->run();


