<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Scraper
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Scraper;



/**
 * This is really a data transformer that transforms an Identity returned from
 * an Auth adapter into an Item that can be properly handled by the whole
 * RemoteResource sub-system in DeskPRO.
 */
class AuthIdentity extends \Orb\Scraper\AbstractScraper
{
	/**
	 * @param mixed $identity Info we're requesting. A URL, an ID, etc. Depends on the scraper.
	 * @return ItemInterface
	 */
	function getData($identity)
	{
		if (!($identity instanceof \Orb\Auth\Identity)) {
			throw new \InvalidArgumentException('$identity must be an Identity');
		}

		$item = new \Orb\Scraper\Item(
			$identity->getIdentity(),
			isset($identity['identity_friendly']) ? $identity['identity_friendly'] : null,
			array(
				'fullname' => isset($identity['fullname']) ? $identity['fullname'] : null,
				'nickname' => isset($identity['nickname']) ? $identity['nickname'] : null,
				'email_addresses' => isset($identity['email_address']) ? array($identity['email_address']) : null,
				'raw_info' => $identity->getRawData()
			)
		);

		return $item;
	}
}