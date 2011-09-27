<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Auth;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Auth\Identity;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonFieldData;
use Application\DeskPRO\Entity\PersonScraper;
use Application\DeskPRO\Entity\PersonScraperAssoc;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\Usersource;

class LoginProcessor
{
	/**
	 * The users identity
	 * @var Orb\Auth\Identity
	 */
	protected $identity;

	/**
	 * The usersource
	 * @var Application\DeskPRO\Entity\Usersource
	 */
	protected $usersource;

	/**
	 * The association
	 * @var Application\DeskPRO\Entity\PersonUsersourceAssoc
	 */
	protected $assoc;

	/**
	 * The person the login represents
	 * @var Application\DeskPRO\Entity\Person
	 */
	protected $person;

	protected $is_new_person = false;


	public function __construct(Usersource $usersource, Identity $identity)
	{
		$this->identity = $identity;
		$this->usersource = $usersource;
	}

	public function getPerson()
	{
		if ($this->person !== null) return $this->person;

		#------------------------------
		# Figure if we have an existing Person mapped, or if its
		# a new Person
		#------------------------------

		$em = App::getOrm();
		$assoc_repos = $em->getRepository('DeskPRO:PersonUsersourceAssoc');

		$em->beginTransaction();

		$this->assoc = $assoc_repos->getIdentityAssociation(
			$this->usersource,
			$this->identity->getIdentity()
		);

		// If we dont have one yet, we're basically initializing a new user
		if (!$this->assoc) {

			$this->is_new_person = true;

			$this->person = new Person();
			$this->person->creation_system = 'web.usersource';

			$mapped_fields = $this->usersource->getFieldsFromIdentity($this->identity);
			if (isset($mapped_fields['name'])) {
				$this->person->name = $mapped_fields['name'];
			}

			$em->persist($this->person);
			$em->flush();

			// New assoc
			$this->assoc = new PersonUsersourceAssoc();
			$this->assoc['person']            = $this->person;
			$this->assoc['usersource']        = $this->usersource;
			$this->assoc['identity']          = $this->identity->getIdentity();
			$this->assoc['identity_friendly'] = $this->identity->getFriendlyIdentity();
			$this->assoc['data']              = $this->identity->getRawData();
			$em->persist($this->assoc);
			$em->flush();
		} else {
			$this->person = $this->assoc['person'];
		}

		$this->person['is_user'] = true;
		$this->person->setLastLoginAt();

		$em->persist($this->person);
		$em->persist($this->assoc);
		$em->flush();
		$em->commit();

		return $this->person;
	}
}
