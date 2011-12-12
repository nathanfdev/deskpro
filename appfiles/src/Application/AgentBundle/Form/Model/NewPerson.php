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
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Organization;

class NewPerson
{
	public $name;
	public $email;

	public $organization_id;
	public $organization_position;

	public $new_organization;

	public $labels = array();
	public $usergroup_ids = array();
	public $custom_fields = array();

	public $timezone;

	protected $_person;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $_em;

	public function __construct(Person $person_context)
	{
		$this->_person_context = $person_context;

		$this->_em = App::getOrm();
	}

	public function setCustomFieldForm(array $form)
	{
		$this->custom_fields = isset($form['newperson']['custom_fields']) ? $form['newperson']['custom_fields'] : array();
	}

	public function save()
	{
		$this->_em->beginTransaction();

		$person = new Person();
		$person->getLabelManager()->setLabelsArray($this->labels);

		if ($this->name) {
			$person->name = $this->name;
		}

		if ($this->email) {
			$person->setEmail($this->email, true);
		}

		if ($this->timezone) {
			$person->timezone = $this->timezone;
		}

		if ($this->organization_id) {
			$org = $this->_em->find('DeskPRO:Organization', $this->organization_id);
			if ($org) {
				$person->organization = $org;
				$person->organization_position = $this->organization_position;
			}
		} elseif ($this->new_organization) {
			$org = new Organization();
			$org->name = $this->new_organization;
			$this->_em->persist($org);

			$person->organization = $org;
			$person->organization_position = $this->organization_position;

		}

		foreach ($this->usergroup_ids as $ug_id) {
			$ug = $this->_em->find('DeskPRO:Usergroup', $ug_id);
			if ($ug_id) {
				$person->usergroups->add($ug);
			}
		}

		$person->creation_system = Person::CREATED_WEB_AGENT;

		$this->_em->persist($person);
		$this->_em->flush();

		if ($this->custom_fields) {
			$user_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
			foreach ($user_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($this->custom_fields) as $info) {
					$d = $person->setCustomData($info[0], $info[1], $info[2]);
					//$this->_em->persist($d);
				}
			}
		}

		$this->_em->flush();
		$this->_em->commit();

		$this->_person = $person;
	}

	public function getPerson()
	{
		return $this->_person;
	}
}
