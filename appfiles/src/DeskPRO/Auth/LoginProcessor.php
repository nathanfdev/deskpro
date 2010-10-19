<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage CoreBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Auth;

use \Orb\Auth\Identity;

use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\PersonEmail;
use \Application\CoreBundle\Entity\PersonScraper;
use \Application\CoreBundle\Entity\PersonScraperAssoc;
use \Application\CoreBundle\Entity\PersonUsersourceAssoc;
use \Application\CoreBundle\Entity\Usersource;

class LoginProcessor
{
	/**
	 * The users identity
	 * @var Orb\Auth\Identity
	 */
	protected $identity;

	/**
	 * The usersource
	 * @var Application\CoreBundle\Entity\Usersource
	 */
	protected $usersource;

	/**
	 * The scraper that is tied to the usersource, if any
	 * @var Application\CoreBundle\Entity\PersonScraper
	 */
	protected $person_scraper;

	/**
	 * The association
	 * @var Application\CoreBundle\Entity\PersonUsersourceAssoc
	 */
	protected $assoc;

	/**
	 * The person the login represents
	 * @var Application\CoreBundle\Entity\Person
	 */
	protected $person;

	protected $is_new_person = false;

	
	public function __construct(Usersource $usersource, Identity $identity)
	{
		$this->identity = $identity;
		$this->usersource = $usersource;
		$this->person_scraper = $usersource['person_scraper'];
	}

	public function getPerson()
	{
		if ($this->person !== null) return $this->person;

		#------------------------------
		# If this is a scrapable usersource (ie we get more info)
		# We should get that info now
		#------------------------------

		if ($this->person_scraper) {
			$scraper_handler = $this->person_scraper->getHandler();
			$scraped_data = $scraper_handler->dataForIdentity($this->identity->getIdentity());
		}

		#------------------------------
		# Figure if we have an existing Person mapped, or if its
		# a new Person
		#------------------------------
		
		$em = App::getOrm();
		$assoc_repos = $em->getRepository('CoreBundle:PersonUsersourceAssoc');

		$em->beginTransaction();

		$this->assoc = $assoc_repos->getIdentityAssociation(
			$this->id,
			$this->identity->getIdentity()
		);

		// If we dont have one yet, we're basically initializing a new user
		if (!$this->assoc) {

			$this->is_new_person = true;

			//TODO: Search based on identifiable info, such as an email address
			//fetched fro $scraper_data, and try to detch.
			//- If a user account already exists it should exception now, and we
			//can handle the UI for that in above layer. User should log in and
			//manually associate their account after using an existing auth scheme.
			//- If Person already exists, but not a user, we promote them into a user

			// New user
			$this->person = new Person();
			if ($this->identity->getFriendlyIdentity()) {
				$this->person['nick_name'] = $this->identity->getFriendlyIdentity();
			}

			$em->persist($this->person);

			// New assoc
			$this->assoc = new PersonUsersourceAssoc();
			$this->assoc['person']            = $this->person;
			$this->assoc['usersource']        = $this->usersource;
			$this->assoc['identity']          = $this->identity->getIdentity();
			$this->assoc['identity_friendly'] = $this->identity->getFriendlyIdentity();
			$this->assoc['data']              = $this->identity->getRawData();

		}

		// TODO: Use ScrapeDataApplicator or whatever, need a strategy
		// TODO: Save scraped data into PersonScraperData (part of the applicator probably)
		// Apply scraped data to the Person object now
		// - Need way to set preference. Like what data source takes precenedence etc
		if ($scraped_data) {
			foreach($scraped_data as $data) {
				if ($data['groupname'] == 'person_email') {
					$email = new PersonEmail();
					$email['email'] = $data['value_text'];
					$email['is_validated'] = true;
					$person->addEmailAddress($email);
				} elseif ($data['groupname'] = 'person_full_name' AND !$this->person['full_name']) {
					$this->person['full_name'] = $data['value_text'];
				} elseif ($data['groupname'] = 'person_nick_name' AND !$this->person['nick_name']) {
					$this->person['nick_name'] = $data['value_text'];
				}
			}
		}

		// TODO
		// Good time to apply "user rules", or post-registration rules.
		// For now, just add new users to the correct Registered usergroup
		if ($this->is_new_person) {
			$usergroup = $this->em->find('CoreBundle:Usergroup', 4);
			$person->addUsergroup($usergroup);
		}

		

		$this->person['is_user'] = true;
		$this->person->setLastLoginAt();
		$this->assoc->setLastUsedAt();

		$em->persist($this->person);
		$em->persist($this->assoc);
		$em->flush();

		$em->commit();

		return $this->person;
	}
}
