<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\pulse\discover;

/**
 * A pulse/syncthing discovery package
 *
 * @link https://github.com/syncthing/syncthing/blob/master/protocol/DISCOVERY.md
 */
class Packet
{
	const MAGIC = 0x9D79BC39;
	const MAGIC_STRING = "\x9D\x79\xBC\x39";

	private $_thisDevice;
	private $_extraDevices = [];

	/**
	 * @return Device
	 */
	public function getDevice()
	{
		return $this->_thisDevice;
	}

	/**
	 * @return Device[]
	 */
	public function getExtraDevices()
	{
		return $this->_extraDevices;
	}

	public function __construct($thisDevice, $extraDevices = [])
	{
		$this->_thisDevice = $thisDevice;
		$this->_extraDevices = $extraDevices;
	}

	/**
	 * Parses a binary representation
	 * @param $packet
	 * @return bool|Packet
	 */
	public static function createFromString($packet, $fromAddress)
	{
		if (strncmp($packet, self::MAGIC_STRING, 4) !== 0) {
			return false;
		}

		// extract "this" device
		$thisDevice = Device::createFromString(substr($packet, 4), $fromAddress, $skip);
		if ($thisDevice === false || strlen($packet) < 4 + $skip + 4) {
			return false;
		}
		$extraCount = unpack('Ncnt', mb_substr($packet, 4 + $skip, 4, '8bit'))['cnt'];
		$packet = mb_substr($packet, 4 + $skip + 4, null, '8bit');

		// extract extra devices
		$extra = [];
		while($extraCount-- > 0) {
			if (($device = Device::createFromString($packet, $fromAddress, $skip)) !== false) {
				$extra[] = $device;
				$packet = mb_substr($packet, $skip, null, '8bit');
			} else {
				return false;
			}
		}
		// ensure packet is fully parsed
		if (!empty($packet)) {
			return false;
		}

		return new self($thisDevice, $extra);
	}

	public function __toString()
	{
		return pack('N', self::MAGIC)
			 . $this->_thisDevice
			 . pack('N', count($this->_extraDevices))
			 . implode('', $this->_extraDevices);
	}


} 