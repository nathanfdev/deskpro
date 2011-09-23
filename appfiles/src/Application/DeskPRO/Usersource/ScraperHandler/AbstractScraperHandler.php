<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Usersource\ScraperHandler;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonScraper;

abstract class ScraperHandlerInterface
{
	/**
	 * Try to detect a remote identity based on a Person object.
	 * A Handler should use data already on file to find a matching record,
	 * for example searching on an email address.
	 *
	 * Note that only a single identity should be returned.
	 *
	 * @return mixed The remote identity or null if no match
	 */
	public function detectIdentityFromPerson(Person $person)
	{
		return null;
	}



	/**
	 * Get all scrapable data for a given identity.
	 *
	 * Return an array of raw data scraped for the identity. Return null
	 * if the identity does not exist.
	 *
	 * @return array
	 */
	public function scrapeIdentity($identity)
	{
		return null;
	}



	/**
	 * Gets an array of data we'll use to apply to a person. This basically
	 * normalizes a scrapers data into standard array we can use.
	 *
	 * @param array $scraper_data
	 */
	public function getPersonData(array $scraper_data)
	{
		return \Application\DeskPRO\Util::getPersonData($scraper_data);
	}
}
