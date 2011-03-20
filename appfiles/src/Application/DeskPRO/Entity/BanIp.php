<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Numbers;

/**
 * Ban an IP addresses and ranges
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\BanIp")
 * @orm:Table(name="ban_ips")
 */
class BanIp extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The banned IP address (human readable)
	 * 
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="banned_ip", type="string", length=100)
	 */
	protected $banned_ip;

	/**
	 * Start of the IP range
	 *
	 * @var int
	 * @orm:Column(name="ip_start", type="bigint")
	 */
	protected $ip_start;

	/**
	 * End of the IP range
	 *
	 * @var int
	 * @orm:Column(name="ip_end", type="bigint")
	 */
	protected $ip_end;

	public function setBannedIp($ip)
	{
		// Dont include wildcard at the ned
		$ip = preg_replace('#\.\*$#', '', $ip);

		// Remove bad chars
		$ip = preg_replace('#[^0-9\.]#', '', $ip);

		// Remove trailin dots
		$ip = trim($ip, '.');

		$parts = explode('.', $ip);
		if (count($parts) < 1 OR count($parts) > 4) {
			throw new \InvalidArgumentException('Invalid IP address: `'.$ip.'`');
		}

		$start = array();
		$end = array();

		foreach ($parts as $part) {
			$start[] = $part;
			$end[] = $part;
		}

		// For wildcarded parts we're missing some octets,
		// so we'll fill them in automatically
		while (count($start) < 4) {
			$start[] = 0;
			$end[] = 255;
		}

		if (count($parts) < 4) {
			$human = implode('.', $parts) . '.*';
		} else {
			$human = implode('.', $parts);
		}

		$this->banned_ip = $human;
		$this->ip_start = sprintf("%u", ip2long(implode('.', $start)));
		$this->ip_end = sprintf("%u", ip2long(implode('.', $end)));
	}
}