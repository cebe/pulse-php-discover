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
			$ip = static::bytes2ip(mb_substr($packet, 4, $len, '8bit'));
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
		$ip = $this->ip2bytes($this->_ip);
		return pack('N', strlen($ip))
			 . $ip
			 . "\x00\x00"
			. pack('n', $this->_port);
	}

	protected static function ip2bytes($ip)
	{
		if (empty($ip)) {
			return $ip;
		}
		if (strpos($ip, ':') === false) {
			$ip = explode('.', $ip);
			return chr($ip[0]) . chr($ip[1]) . chr($ip[2]) . chr($ip[3]);
		} elseif (strpos($ip, '.') === false) {
			// TODO fill empty parts!
			return hex2bin(str_replace(':', '', $ip));
		} else {
			// TODO IPv6 with v4 part in it ::ffff:192.168.178.50
			return $ip;
		}
	}

	protected static function bytes2ip($ip)
	{
		if (strlen($ip) === 4) {
			// ipv4
			return ord($ip[0]) . '.'
				. ord($ip[1]) . '.'
				. ord($ip[2]) . '.'
				. ord($ip[3]);
		} else {
			// ipv6
			return preg_replace('/([a-f\d]{4})/', '\\1:', bin2hex($ip));
		}
	}
}