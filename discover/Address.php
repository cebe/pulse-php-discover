<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\pulse\discover;


class Address
{
	private $_ip = '';
	private $_port = 0;

	public function getIp()
	{
		return $this->_ip;
	}

	public function getPort()
	{
		return $this->_port;
	}

	public function __construct($ip, $port)
	{
		$this->_ip = $ip;
		$this->_port = $port;
	}

	public static function createFromString($packet, $fromAddress, &$length = null)
	{
		if (strlen($packet) < 4) {
			return false;
		}

		// extract length for IP
		$len = unpack('Nlen', mb_substr($packet, 0, 4, '8bit'))['len'];
		if ($len === 0) {
			$ip = $fromAddress;
		} else if (strlen($packet) < 4 + $len + 4) {
			// if not at least the IP + 1 word are there -> fail
			return false;
		} else {
			$ip = mb_substr($packet, 4, $len, '8bit'); // TODO verify input whether it is a valid ip address
		}

		// extract port
		if (strncmp(mb_substr($packet, 4 + $len, 2, '8bit'), "\x00\x00", 2) !== 0) {
			// next two bytes before port must be 0
			return false;
		}
		$port = unpack('nport', mb_substr($packet, 4 + $len + 2, 2, '8bit'))['port'];

		$length = 4 + $len + 4;
		return new self($ip, $port);
	}

	public function __toString()
	{
		return pack('N', strlen($this->_ip))
			 . $this->_ip
			 . "\x00\x00"
			. pack('n', $this->_port);
	}
}