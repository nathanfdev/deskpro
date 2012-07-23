<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DeskPRO
 */

namespace Application\DeskPRO\Auth;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Auth\Identity;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
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

		// If we dont have one yet, we're have to create the assoc and maybe a new user too
		if (!$this->assoc) {

			$mapped_fields = $this->usersource->getFieldsFromIdentity($this->identity);
			$mapped_fields = Arrays::removeEmptyString($mapped_fields);
			$mapped_fields = new \Orb\Util\OptionsArray($mapped_fields);

			$this->person = null;

			// If we can trust the email address and there already exists a person
			// with this email address, then we can just link the accounts now
			$set_email = false;
			if ($mapped_fields->has('email') && $mapped_fields->get('email_confirmed')) {
				$set_email = $mapped_fields->get('email');
				$email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($mapped_fields->get('email'));
				if ($email) {
					$this->person = $email->person;
				}
			}

			if (!$this->person) {
				$this->person = new Person();
				$this->person->is_user = true;
				$this->person->creation_system = 'web.usersource';
			}

			foreach (array('first_name', 'last_name', 'name') as $k) {
				if (!$this->person[$k] && $mapped_fields->has($k)) {
					$this->person[$k] = $mapped_fields->get($k);
				}
			}

			$em->persist($this->person);
			$em->flush();

			if ($set_email && !$this->person->findEmailAddress($set_email)) {
				$email_obj = $this->person->addEmailAddressString($set_email);
				$em->persist($email_obj);
				$em->flush();
			}

			// New assoc
			$this->assoc = new PersonUsersourceAssoc();
			$this->assoc['person']            = $this->person;
			$this->assoc['usersource']        = $this->usersource;
			$this->assoc['identity']          = $this->identity->getIdentity();
			$this->assoc['identity_friendly'] = $this->identity->getFriendlyIdentity() ?: $this->identity->getIdentity();
			$this->assoc['data']              = $this->identity->getRawData();
			$em->persist($this->assoc);
			$em->flush();
		} else {
			$this->person = $this->assoc['person'];
		}

		// Update custom field data
		App::getSystemService('person_fields_manager')->copyUsersourceData(
			$this->person,
			$this->identity,
			$this->usersource
		);

		$this->person['is_user'] = true;
		$this->person->setLastLoginAt();

		$em->persist($this->person);
		$em->persist($this->assoc);
		$em->flush();
		$em->commit();

		return $this->person;
	}
}
