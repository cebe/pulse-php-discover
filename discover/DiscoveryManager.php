<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\pulse\discover;


use React\EventLoop\LoopInterface;

class DiscoveryManager
{
	public $interval = 5; // sec

	public $discoveryPort = 21025;


	public $servicePort = 22000;

	protected $knownHosts = [];

	/**
	 * @param Packet $packet
	 */
	public function handlePacket($packet)
	{
		$devices = $packet->getExtraDevices();
		$devices[] = $packet->getDevice();

		/** @var Device $device */
		foreach($devices as $device) {
			$id = $device->getId(false);
			echo "new host: " . $device->getId(true) . ' ' . $device->getAddresses()[0]->getIp() . ':' . $device->getAddresses()[0]->getPort() . "\n";
			if (!isset($this->knownHosts[$id])) {
//				echo "new host: " . $device->getId(true) . ' ' . $device->getAddresses()[0]->getIp() . ':' . $device->getAddresses()[0]->getPort() . "\n";
				$this->knownHosts[$id] = $device;
			} else {
				// TODO update address
			}
		}
	}

	/**
	 * Start servers for UDP Discovery and set timer for sending packets
	 * @param LoopInterface $loop
	 */
	public function start($loop)
	{
		$factory = new \React\Datagram\Factory($loop);

		// IPv4 Server
		$factory->createServer('0.0.0.0:' . $this->discoveryPort)->then(function (\React\Datagram\Socket $client) {
			$client->on('message', function ($message, $serverAddress, $client) {
//				echo 'received ip4 "' . bin2hex($message) . '" from ' . $serverAddress . PHP_EOL;
				$this->handlePacket(Packet::createFromString($message, explode(':', $serverAddress)[0]));
			});
		});
		// IPv6 Server
		$factory->createServer('[::]:' . $this->discoveryPort)->then(function (\React\Datagram\Socket $client) {
			$client->on('message', function ($message, $serverAddress, $client) {
//				echo 'received ip6 "' . bin2hex($message) . '" from ' . $serverAddress . PHP_EOL;
				$this->handlePacket(Packet::createFromString($message, explode(':', $serverAddress)[0]));
			});
		});

		$loop->addPeriodicTimer($this->interval, function() {

			// startup
			$packet = new Packet(new Device('ZGJH6N3NKUXKAZIGHMYI4UQ5LAHDVIT3GSPGGD54HCKJUF3Z23NMIEAA', [new Address('', 1338)])); // 192.168.178.50
//			echo "sending $packet\n";
//			echo "sending " . bin2hex($packet) . "\n";
			// TODO this is blocking IO!
			$this->inet_broadcast($packet);
			$this->inet6_broadcast($packet);

		});

		// TODO remove known hosts after some time
	}



	private function inet_broadcast($packet)
	{
		if (($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
			echo "socket() failed, error: " . socket_strerror($sock) . "\n";
		}
		$opt_ret = socket_set_option($sock, 1, SO_BROADCAST, true);
		if($opt_ret < 0) {
		    echo "setsockopt() failed, error: " . socket_strerror($opt_ret) . "\n";
		}
		foreach($this->determine_broadcast_address() as $address) {
			$send_ret = socket_sendto($sock, $packet, strlen($packet), 0, $address, 21025);
		}
	}


	private function inet6_broadcast($packet)
	{
		if (($sock = socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP)) === false) {
			echo "socket() failed, error: " . socket_strerror($sock) . "\n";
		}
		$opt_ret = socket_set_option($sock, 1, SO_BROADCAST, true);
		if($opt_ret < 0) {
		    echo "setsockopt() failed, error: " . socket_strerror($opt_ret) . "\n";
		}
		foreach($this->determine_broadcast_address6() as $address) {
			$send_ret = socket_sendto($sock, $packet, strlen($packet), 0, $address, 21025);
		}
	}


	private function determine_broadcast_address()
	{
		exec("ifconfig | grep Bcast | cut -d \":\" -f 3 | cut -d \" \" -f 1", $addr);
		$addr[] = '127.0.0.1';
		return $addr;
	}


	private function determine_broadcast_address6()
	{
	//	exec("ifconfig | grep Bcast | cut -d \":\" -f 3 | cut -d \" \" -f 1", $addr);
		$addr = [];
		$addr[] = 'ff01::1';
		return $addr;
	}

} 