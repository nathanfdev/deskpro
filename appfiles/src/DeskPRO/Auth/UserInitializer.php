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

use \Application\CoreBundle\Entity\PersonData;
use \Application\CoreBundle\Entity\PersonField;
use \Application\CoreBundle\Entity\Usersource;

class UserInitializer
{
	/**
	 * Entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;

	public function __construct(\DeskPRO\ORM\EntityManager $em)
	{
		$this->em = $em;
	}


	
	/**
	 * Get a Person record form an Identity we got from some usersource. This will either return
	 * an existing Person, or it will initialize a new person based on the data from the identity.
	 * 
	 * @param int $usersource_id
	 * @param Identity $identity
	 * @return Person
	 */
	public function getPersonFromIdentity($usersource_id, Identity $identity)
	{
		// ID 0 means built-in login, we can just return the
		// 'person' object
		if (!$usersource_id) {
			if (!isset($identity['person'])) return null;
			return $identity['person'];
		}

		$usersource = $this->em->find('CoreBundle:Usersource', $usersource_id);
		$person_id = $this->findPersonIdFromField($usersource['person_field'], $identity->getIdentity());

		if ($person_id) {
			$person = $this->em->find('CoreBundle:Person', $person_id);
		} else {
			$person = $this->createPersonFromIdentity($usersource, $identity);
		}

		return $person;
	}


	/**
	 * Search for a Person ID based off of the remote ID stored in the PersonData
	 * table.
	 *
	 * @param Application\CoreBundle\Entity\PersonField $person_field
	 * @param mixed $find_value
	 * @return int
	 */
	public function findPersonIdFromField(PersonField $person_field, $find_value)
	{
		try {
			$person_data = $this->em->getRepository('CoreBundle:PersonData')->findOneBy(array(
				'person_field_id' => $person_field['id'],
				'value' => $find_value
			));
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

		return $person_data['person_id'];
	}

	

	/**
	 * Create a new Person object based on an Identity
	 *
	 * @param Usersource $usersource
	 * @param Identity $identity
	 */
	public function createPersonFromIdentity(Usersource $usersource, Identity $identity)
	{
		$this->em->beginTransaction();

		// Person
		$person = $this->em->createEntity('CoreBundle:Person');

		if ($identity->getName()) {
			$person['fullname'] = $identity->getName();
		}
		if ($identity->getNickname()) {
			$person['nickname'] = $identity->getNickname();
		}

		// Any email addresses
		if ($identity->getEmailAddresses()) {
			$pref = 0;
			foreach ($identity->getEmailAddresses() as $email_address) {
				$email = $this->em->createEntity('CoreBundle:PersonEmail');
				$email['email_address'] = $email_address;
				$email['pref_order'] = $pref++;
				$email['is_validated'] = true;
				$person['email_addresses']->add($email);
			}
		}

		// Add to users usergroup
		$user_usergroup = $this->em->find('CoreBundle:Usergroup', 3);
		$person['usergroups']->add($user_usergroup);

		$this->em->persist($person);
		$this->em->flush();

		// Create the PersonData field
		$person_data = $this->em->createEntity('CoreBundle:PersonData');
		$person_data['person_field'] = $user_usergroup['person_field'];
		$person_data['value'] = $identity->getIdentity();
		$person_data['person'] = $person;
		$this->em->persist($person_data);
		$this->em->flush();

		$this->em->commit();

		return $person;
	}
}