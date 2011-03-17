<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;
use \Application\DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity\UsergroupPropertyPermission;
use \Application\DeskPRO\Entity;


/**
 * An organization is a grouping we put similar people into (eg companies).
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Organization")
 * @orm:Table(name="organizations")
 */
class Organization extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The organization name
	 *
	 * @var string
	 * @orm:Column(name="name", type="string", length=255)
	 */
	protected $name = null;

	/**
	 * @orm:OneToMany(targetEntity="CustomDataOrganization", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;
	
	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TaskAssociatedOrganization", mappedBy="organization")
	 */
	protected $task_associations;
	
	

	/**
	 * @orm:OneToMany(targetEntity="LabelOrganization", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="OrganizationContactData", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $contact_data;

	protected $_label_manager = null;

	public function __construct()
	{
		$this->custom_data         = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels              = new \Doctrine\Common\Collections\ArrayCollection();
		$this->contact_data        = new \Doctrine\Common\Collections\ArrayCollection();
		$this->task_associations   = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * Find an existing data record for a field id.
	 *
	 * @param int $field_id
	 * @return CustomDataOrganization
	 */
	public function getCustomDataForField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data['field_id'] == $field_id) {
				return $data;
			}
		}

		return null;
	}



	/**
	 * Set custom field data for a particular field.
	 *
	 * @param int $field_id
	 * @param mixed $value
	 * @return mixed
	 */
	public function setCustomData($field_id, $value_type, $value)
	{
		$custom_data = $this->getCustomDataForField($field_id);
		$is_new = false;

		if (!$custom_data) {
			if ($value === null) return null;

			$is_new = true;

			$field = App::getEntityRepository('DeskPRO:CustomDefOrganization')->find($field_id);
			if (!$field) {
				throw new \Exception("Invalid field_id `$field_id`");
			}
			$custom_data = new CustomDataOrganization();
			$custom_data['field'] = $field;
		}

		if ($value === null) {
			$this['custom_data']->removeElement($custom_data);
			return null;
		}

		$custom_data[$value_type] = $value;

		if ($is_new) {
			$this->addCustomData($custom_data);
		}

		return $custom_data;
	}

	/**
	 * Add a custom data item to this ticket
	 *
	 * @param CustomDataTicket $data
	 */
	public function addCustomData(CustomDataOrganization $data)
	{
		$this->custom_data->add($data);
		$data['organization'] = $this;
	}


	/**
	 * Render a custom field
	 */
	public function renderCustomField($field_id, $context = 'html')
	{
		$f_def = App::getEntityRepository('DeskPRO:CustomDefOrganization')->find($field_id);

		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, array($f_def));

		$value = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
		$rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

		return $rendered;
	}



	/**
	 * Add a label
	 * @param Entity\LabelOrganization $label
	 */
	public function addLabel(Entity\LabelOrganization $label)
	{
		$label['organization'] = $this;
		$this->labels->add($label);
	}


	
	/**
	 * Add contact data
	 *
	 * @param OrganizationContactData $contact_data
	 */
	public function addContactData(OrganizationContactData $contact_data)
	{
		$em = App::getOrm();

		$this['contact_data']->add($contact_data);

		$contact_data['organization'] = $this;
		$em->persist($contact_data);
	}


	public function getContactDataOfType($type)
	{
		if (strpos($type, 'Application\\DeskPRO\\') !== 0) {
			$type = \Application\DeskPRO\Form\ContactFieldHandler\AbstractContactFieldHandler::simpleNameToClassName($type);
		}

		$ret = array();
		foreach ($this->contact_data as $contact_data) {
			if ($contact_data['handler_class'] == $type) {
				$ret[] = $contact_data;
			}
		}

		return $ret;
	}

	public function getIms()
	{
		return $this->getContactDataOfType('instant_message');
	}

	public function getAddresses()
	{
		return $this->getContactDataOfType('address');
	}

	public function getPhones()
	{
		return $this->getContactDataOfType('phone');
	}


	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelOrganization');
		}

		return $this->_label_manager;
	}
}
