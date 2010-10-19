<?php

namespace DeskPRO\Usersource;

use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\PersonScraper;
use \Application\CoreBundle\Entity\PersonScraperAssoc;
use \Application\CoreBundle\Entity\PersonUsersourceAssoc;
use \Application\CoreBundle\Entity\Usersource;

class ScrapeDataUpdater
{
	/**
	 * The person we want to update
	 * @var Application\CoreBundle\Entity\Person
	 */
	protected $person;
	protected $all_scrapers;

	public function __construct(Person $person)
	{
		$this->person;

		$em = App::getOrm();

		$this->all_scrapers = $em->createQuery("
			SELECT s
			FROM CoreBundle:PersonScraper INDEXBY s.id
			WHERE s.is_enabled = ?1
		")->setParam(1, true)->getResults();
	}

	

	/**
	 * Find all scrapers whose polling says we should update, and run them.
	 */
	public function runApplicableScrapers()
	{
		$run = array_merge($this->getDiscoverable(), $this->getUpdatable());

		foreach ($run as $s) {
			$this->runScraper($s);
		}
	}


	
	/**
	 * Run a scraper to update info on the Person
	 * 
	 * @param PersonScraper $scraper
	 * @return string
	 */
	public function runScraper(PersonScraper $scraper)
	{
		$em = App::getOrm();
		$em->beginTransaction();

		$scraper_handler = $scraper->getHandler();

		$assoc = $this->findAssocForScraper($scraper);

		// No id, we're running discovery
		if (!$assoc OR !$assoc['identity']) {
			$identity = $scraper_handler->detectIdentityFromPerson($this->person);

			$assoc = new PersonScraperAssoc();
			$assoc['person'] = $this->person;
			$assoc['scraper'] = $scraper;
			$assoc['identity'] = $identity;
			
			$this->person['personscraper_assoc']->add($assoc);

			$em->persist($assoc);
			$em->flush();

			// Still no identity
			if (!$identity) {
				$em->commit();
				return;
			}

		} else {
			$identity = $assoc['identity'];
		}

		// Run the info scraper now
		$scraper_data = $scraper_handler->scrapeIdentity($identity);

		$this->applyScraperData($assoc, $identity, $scraper_data);
	}


	
	/**
	 * Applies scraper data
	 *
	 * @param PersonScraper $scraper
	 * @param string $identity
	 * @param array $scraper_data
	 */
	public function applyScraperData(PersonScraperAssoc $assoc, $identity, array $scraper_data)
	{
		$em = App::getOrm();
		$em->beginTransaction();
		
		// Null means the id doesnt exist, so we should delete the scraper assoc
		// TODO: Implement search/moved discovery? (ie LDAP use DN, but the user tree was moved?)
		if ($scraper_data === null) {
			$em->remove($assoc);
			$em->flush();
			$em->commit();
			return;
		}

		$assoc['raw_data'] = $scraper_data;

		// Save scraper data
		$scraper_data_objs = $assoc['scraper']->getHandler()->createDataRecords($scraper_data);
		if ($scraper_data_objs) {
			foreach ($scraper_data_objs as $data_obj) {
				$em->persist($data_obj);
				$assoc->addData($data_obj);
			}
		}

		$em->persist($assoc);
		$em->flush();
		$em->commit();
	}


	
	public function findAssocForScraper(PersonScraper $scraper)
	{
		$found_assoc = false;
		foreach ($this->person['personscraper_assoc'] as $assoc) {
			if ($assoc['scraper_id'] == $scraper['id']) {
				$found_assoc = $assoc;
				break;
			}
		}

		return $found_assoc;
	}



	/**
	 * Get an array of scrapers that are scheduled to update now.
	 *
	 * @return array
	 */
	public function getUpdatable()
	{
		$updatable = array();

		$time = time();

		foreach ($this->person['personscraper_assoc'] as $assoc) {
			// No identity means its just a disovery timer
			if (!$assoc['identity']) continue;

			$lifetime = $assoc['scraper']->poll_interval;
			if (!$lifetime) continue;

			$expire_time = $time - $lifetime;

			if ($assoc['updated_at']->getTimestamp() < $expire_time) {
				$updatable[] = $assoc['scraper'];
			}
		}

		return $updatable;
	}

	

	/**
	 * Get an array of scrapers that we should try auto-discovery again.
	 * 
	 * @return array
	 */
	public function getDiscoverable()
	{
		$discoverable = array();

		$time = time();

		foreach ($this->all_scrapers as $scraper) {

			// No auto discovery
			if (!$scraper->poll_discovery_interval) continue;

			$found_assoc = $this->findAssocForScraper($scraper);

			if ($found_assoc) {
				// An existing identity means we already know about it
				if ($found_assoc['identity']) continue;

				$lifetime = $scraper->poll_discovery_interval;
				$expire_time = $time - $lifetime;

				if ($found_assoc['updated_at']->getTimestamp() < $expire_time) {
					$discoverable[] = $scraper;
				}
			} else {
				// New scraper, means we're implicitly "ready" to discover
				$discoverable[] = $scraper;
			}
		}

		return $discoverable;
	}
}