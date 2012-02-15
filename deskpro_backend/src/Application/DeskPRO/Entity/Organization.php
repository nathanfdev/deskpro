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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

use Application\DeskPRO\Entity\UsergroupPropertyPermission;
use Application\DeskPRO\Entity;


/**
 * An organization is a grouping we put similar people into (eg companies).
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Organization")
 * @ORM_Mapping\Table(name="organizations")
 */
class Organization extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * The org picture
	 *
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\OneToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="picture_blob_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $picture_blob = null;

	/**
	 * The organization name
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 */
	protected $name = null;

	/**
	 * The summary field as filled in by agents
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="summary", type="text")
	 */
	protected $summary = '';

	/**
	 * The org importance
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="importance", type="integer")
	 */
	protected $importance = 0;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="CustomDataOrganization", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;

	/**
	 * Usergroups the user belongs to
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Usergroup", indexBy="id")
	 * @ORM_Mapping\JoinTable(name="organization2usergroups",
	 *     joinColumns={@ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")}
     * )
	 */
	protected $usergroups;

	/**
	 * Users who are set to automatically be added to tickets and other org things
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Person", indexBy="id")
	 * @ORM_Mapping\JoinTable(name="organizations_auto_cc",
	 *     joinColumns={@ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")}
     * )
	 */
	protected $auto_cc_people;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelOrganization", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="OrganizationContactData", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true, indexBy="id")
	 */
	protected $contact_data;

	/**
	 * The date the org was inserted into the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	protected $_label_manager = null;

//        /**
//	 * The deals created by this user.
//	 * @var \Application\DeskPRO\Entity\Deal
//	 * @ORM_Mapping\ManyToOne(targetEntity="Deal", cascade={"persist", "remove", "merge"})
//	 */
//	protected $deal;

	public function __construct()
	{
		$this->custom_data         = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels              = new \Doctrine\Common\Collections\ArrayCollection();
		$this->contact_data        = new \Doctrine\Common\Collections\ArrayCollection();
		$this->date_created        = new \DateTime();
                //$this->deal      = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * Set the default importance of people in this org
	 *
	 * @param int $importance
	 */
	public function setImportance($importance)
	{
		$old = $this->importance;
		$this->importance = Numbers::bound($importance, 0, 5);
		$this->_onPropertyChanged('importance', $old, $this->importance);
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
	 * Check if this ticket has a custom field.
	 *
	 * @param $field_id
	 * @return bool
	 */
	public function hasCustomField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data->field['id'] == $field_id) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Gets a display array for a specific field
	 * @param $field_id
	 * @return array|mixed|null
	 */
	public function getCustomFieldDisplayArray($field_id)
	{
		$data = $this->getCustomDataForField($field_id);
		if (!$data) {
			return null;
		}

		$org_field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();
		$org_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(array($data), $org_field_defs);

		$custom_fields = App::getApi('custom_fields.organizations')->getFieldsDisplayArray(
			$org_field_defs,
			$org_data_structured
		);

		$custom_fields = array_pop($custom_fields);

		return $custom_fields;
	}


	/**
	 * Gets the URL to a picture for the org. If there is no picture for the org, a default one
	 * will be rendered. Use hasPicture if you need to know if a picture exists
	 *
	 * @return null|string
	 */
	public function getPictureUrl($size = 80, $secure = null)
	{
		// Null means detect
		if ($secure === null AND App::isWebRequest()) {
			$request = App::getRequest();
			if ($request->isSecure()) {
				$secure = true;
			}
		}

		$url = false;
		if ($this->picture_blob) {
			$url = App::get('router')->generate('serve_blob', array(
				'blob_auth_id' => $this->picture_blob->getAuthId(),
				's' => $size
			), true);
		}

		if (!$url) {
			$url = App::get('router')->generate('serve_org_picture_default', array(
				's' => $size,
			), true);
		}

		if ($secure) {
			$url = preg_replace('#^http:#', 'https:', $url);
		}

		return $url;
	}



	/**
	 * Cehck if the company has a picture uploaded
	 *
	 * @return bool
	 */
	public function hasPicture()
	{
		if ($this->picture_blob) {
			return true;
		}

		return false;
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

	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelOrganization');
		}

		return $this->_label_manager;
	}

	public function __toString()
	{
		return $this->name;
	}
}
