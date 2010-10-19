<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Usersource\ScraperHandler;

use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\PersonScraper;

abstract class ScraperHandlerInterface
{
	protected $person_scraper;
	
	public function __construct(PersonScraper $person_scraper)
	{
		$this->person_scraper = $person_scraper;
	}

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
	 * Should return an array of PersonScraperData objects, or null if this is not supported.
	 *
	 * @return array
	 */
	public function dataForIdentity($identity)
	{
		return null;
	}
}