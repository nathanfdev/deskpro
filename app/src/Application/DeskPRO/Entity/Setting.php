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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Setting")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="settings")
 */
class Setting extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;


	/**
	 * The name of the setting
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 */
	protected $name = null;


	/**
	 * Settings can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.url, the group is 'deskpro'
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;


	/**
	 * The value of a setting
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="value", type="text", nullable=true)
	 */
	protected $value;


	/**
	 * The default value set by DeskPRO.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="default_value", type="text")
	 */
	protected $default_value = '';


	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;



	/**
	 * Get the value of a setting.
	 *
	 * @return string
	 */
	public function getValue()
	{
		if ($this->value === null) {
			return $this->default_value;
		}

		return $this->value;
	}


	/**
	 * @ORM_Mapping\PrePersist
	 * @ORM_Mapping\PreUpdate
	 */
	public function _resetValueIfDefault()
	{
		if ($this->value == $this->default_value) {
			$this->value = null;
		}
	}

	/**
	 * @ORM_Mapping\PrePersist
	 * @ORM_Mapping\PreUpdate
	 */
	public function _resetGroupFromName()
	{
		$dotpos = strpos($this->name, '.');
		if ($dotpos) {
			$this->groupname = substr($this->name, 0, $dotpos);
		} else {
			$this->groupname = null;
		}
	}

	/** @ORM_Mapping\PrePersist */
	public function _incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @ORM_Mapping\PreUpdate */
	public function _incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}
