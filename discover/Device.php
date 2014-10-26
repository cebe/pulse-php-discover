<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\pulse\discover;


class Device
{
	private $_id;
	private $_addresses;

	/**
	 * @param bool $pretty
	 * @return string
	 */
	public function getId($pretty = false)
	{
		return $pretty ? rtrim(preg_replace('/([\w\d]{7})/', '\\1-', $this->_id), '-') : $this->_id;
	}

	/**
	 * @return Address[]
	 */
	public function getAddresses()
	{
		return $this->_addresses;
	}

	public function __construct($id, $addresses = [])
	{
		$this->_id = $id;
		$this->_addresses = $addresses;
	}

	public static function createFromString($packet, $fromAddress, &$length = null)
	{
		if (strlen($packet) < 4) {
			return false;
		}

		// extract id
		$len = unpack('Nlen', mb_substr($packet, 0, 4, '8bit'))['len'];
		if (strlen($packet) < 4 + $len + 4) {
			return false;
		}
		$id = rtrim(\Base32\Base32::encode(substr($packet, 4, $len)), '=');

		// extract addresses
		$addrCount = unpack('Ncnt', mb_substr($packet, 4 + $len, 4, '8bit'))['cnt'];
		$packet = mb_substr($packet, 4 + $len + 4, null, '8bit');
		$length = 4 + $len + 4;
		$addresses = [];
		while($addrCount-- > 0) {
			if (($addr = Address::createFromString($packet, $fromAddress, $skip)) !== false) {
				$addresses[] = $addr;
				$packet = mb_substr($packet, $skip, null, '8bit');
				$length += $skip;
			} else {
				$length = null;
				return false;
			}
		}
		return new self($id, $addresses);
	}

	public function __toString()
	{
		$id = \Base32\Base32::decode(strtoupper(str_replace('-', '', $this->_id)));
		return pack('N', strlen($id))
			 . $id
			 . pack('N', count($this->_addresses))
			 . implode('', $this->_addresses);
	}
}