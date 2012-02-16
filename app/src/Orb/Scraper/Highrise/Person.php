<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Scraper
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Scraper\Highrise;

/**
 * Scrapes person data
 */
class Person extends AbstractScraper
{
	/**
	 * @var Orb\Service\Highrise\Highrise
	 */
	protected $highrise;


	
	/**
	 * @param int $person_id
	 * @return ItemInterface
	 */
	function getData($person_id)
	{
		if ($this->highrise === null) {
			$this->highrise = new \Orb\Service\Highrise\Highrise(
				$this->getOption('highrise_url'),
				$this->getOption('highrise_auth_token')
			);
		}

		$data = $this->highrise->person->getPerson($person_id);

		$item = new \Orb\Scraper\Item(
			$person_id,
			trim($data['first-name'] . ' ' . $data['last-name']),
			$data
		);

		return $item;
	}
}