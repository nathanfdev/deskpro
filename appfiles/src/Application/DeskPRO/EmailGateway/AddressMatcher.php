<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Doctrine\ORM\EntityManager;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

use Application\DeskPRO\Entity\EmailGatewayAddress;
use Application\DeskPRO\Entity\EmailGateway;

/**
 * This looks at an email address ('to') and tries to match it against an EmailGatewayAddress.
 * If no address matches, then the default address for a gateway is returned.
 *
 */
class AddressMatcher
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var array
	 */
	protected $patterns = null;


	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}


	/**
	 * Load all the email address patterns from the databaase
	 *
	 * @return array
	 */
	public function getPatterns()
	{
		if ($this->patterns !== null) return $this->patterns;

		$patterns = $this->db->fetchAll("
			SELECT a.id, a.email_gateway_id, a.match_type, a.match_pattern
			FROM email_gateway_addresses a
			LEFT JOIN email_gateways g ON (g.id = a.email_gateway_id)
			WHERE g.is_enabled
		");

		// Order them into exact, domain, pattern
		$this->patterns = array();
		foreach ($patterns as $p) if ($p['match_type'] == 'exact')  $this->patterns[] = $p;
		foreach ($patterns as $p) if ($p['match_type'] == 'domain') $this->patterns[] = $p;
		foreach ($patterns as $p) if ($p['match_type'] == 'regex')  $this->patterns[] = $p;

		return $this->patterns;
	}


	/**
	 * Given an email address, run through the known email address to try and find its gateway.
	 * If no address matches then null is returned unless $gateway was specified, in which case it's default address is returned.
	 *
	 * @param string $address
	 * @param \Application\DeskPRO\Entity\EmailGateway $gateway  The gateway the message was found in. The default address will be used if none matching.
	 * @return \Application\DeskPRO\Entity\EmailGatewayAddress
	 */
	public function getMatchingAddress($address, Emailgateway $gateway = null)
	{
		$this->getPatterns();

		$address = strtolower($address);

		$match_address_id = null;
		foreach ($this->patterns as $pattern) {
			switch ($pattern['match_type']) {
				case 'exact':
					if ($address == $pattern['match_pattern']) {
						$match_address_id = $pattern['id'];
						break 2;
					}
					break;

				case 'domain':
					list (, $domain) = explode('@', $address);
					if ($domain == $pattern['match_pattern']) {
						$match_address_id = $pattern['id'];
						break 2;
					}
					break;

				case 'regex':
					$regex = trim($pattern['match_pattern'], '/');
					if (preg_match('/' . $regex . '/i', $address)) {
						$match_address_id = $pattern['id'];
						break;
					}
			}
		}

		if (!$match_address_id) {
			if ($gateway && $gateway->default_address) {
				return $gateway->default_address;
			}
			return null;
		}

		$matched_address = $this->em->find('DeskPRO:EmailGatewayAddress', $match_address_id);
		return $matched_address;
	}


	/**
	 * given a mail reader, search through To and Cc address to find a matching helpdesk address.
	 *
	 * @param Reader\AbstractReader $reader
	 * @param \Application\DeskPRO\Entity\EmailGateway $gateway
	 * @return \Application\DeskPRO\Entity\EmailGatewayAddress
	 */
	public function getMatchingAddressFromReader(AbstractReader $reader, Emailgateway $gateway = null)
	{
		foreach ($reader->getToAddresses() as $email) {
			$address = $email->getEmail();

			$matched_address = $this->getMatchingAddress($address);
			if ($matched_address) {
				return $matched_address;
			}
		}

		foreach ($reader->getCcAddresses() as $email) {
			$address = $email->getEmail();

			$matched_address = $this->getMatchingAddress($address);
			if ($matched_address) {
				return $matched_address;
			}
		}

		if ($gateway && $gateway->default_address) {
			return $gateway->default_address;
		}

		return null;
	}
}
