<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

class SettingsProfile
{
	public $name;
	public $email;
	public $timezone = 'UTC';
	public $password = '';
	public $password2 = '';
	public $ticket_signature = '';
	public $new_picture_blob_id = false;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(Person $person)
	{
		$this->em = App::getOrm();

		$this->person = $person;

		$this->name = $person->name;
		$this->email = $person->getPrimaryEmailAddress();
		$this->timezone = $person->timezone;
		$this->ticket_signature = $person->getPref('agent.ticket_signature');
	}

	public function requiresAuth()
	{
		if ($this->password || $this->email != $this->person->getPrimaryEmailAddress()) {
			return true;
		}

		return false;
	}

	public function save()
	{
		$this->em->beginTransaction();

		$person = $this->person;

		try {
			$person->name = $this->name;
			$person->timezone = $this->timezone;

			if ($this->new_picture_blob_id) {
				$blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthId($this->new_picture_blob_id);
				if ($blob) {
					$person->picture_blob = $blob;
				}
			}

			$primary_email = $person->getPrimaryEmail();
			if ($primary_email->email != $this->email) {
				$new_primary_email = new \Application\DeskPRO\Entity\PersonEmail();
				$new_primary_email->email = $this->email;
				$new_primary_email->is_validated = true;
				$person->addEmailAddress($new_primary_email);

				$this->em->persist($new_primary_email);

				$person->removeEmailAddressId($primary_email->id);
				$this->em->remove($primary_email);
			}

			if ($this->password) {
				$person->setPassword($this->password);
			}

			$person->setPreference('agent.ticket_signature', $this->ticket_signature);

			$this->em->persist($person);
			$this->em->flush();
			$this->em->commit();

		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}
}
