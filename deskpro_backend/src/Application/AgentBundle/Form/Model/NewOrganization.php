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
use Application\DeskPRO\Entity\Organization;

class NewOrganization
{
	public $name;

	public $labels = array();
	public $usergroup_ids = array();
	public $custom_fields = array();

	protected $_org;

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
		$this->custom_fields = isset($form['neworg']['custom_fields']) ? $form['neworg']['custom_fields'] : array();
	}

	public function save()
	{
		$this->_em->beginTransaction();

		$org = new Organization();
		$org->getLabelManager()->setLabelsArray($this->labels);

		$org->name = $this->name;

		foreach ($this->usergroup_ids as $ug_id) {
			$ug = $this->_em->find('DeskPRO:Usergroup', $ug_id);
			if ($ug_id) {
				$org->usergroups->add($ug);
			}
		}

		$this->_em->persist($org);
		$this->_em->flush();

		if ($this->custom_fields) {
			$user_field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();
			foreach ($user_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($this->custom_fields) as $info) {
					$d = $org->setCustomData($info[0], $info[1], $info[2]);
					$this->_em->persist($d);
				}
			}
		}

		$this->_em->flush();
		$this->_em->commit();

		$this->_org = $org;
	}

	public function getOrganization()
	{
		return $this->_org;
	}
}
