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
	 * @orm:Column(name="name", type="text")
	 */
	protected $name = null;

	/**
	 * @orm:OneToMany(targetEntity="CustomDataOrganization", mappedBy="organization", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;

	public function __construct()
	{
		$this->custom_data         = new \Doctrine\Common\Collections\ArrayCollection();
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

}
