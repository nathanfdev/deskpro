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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Setting")
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="settings")
 */
class Setting extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The name of the setting
	 *
	 * @var string
	 * @orm:Column(name="name", type="string", length=255)
	 */
	protected $name = null;


	/**
	 * Settings can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.url, the group is 'deskpro'
	 *
	 * @var string
	 * @orm:Index
	 * @orm:Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;


	/**
	 * The value of a setting
	 *
	 * @var string
	 * @orm:Column(name="value", type="text", nullable=true)
	 */
	protected $value;


	/**
	 * The default value set by DeskPRO.
	 *
	 * @var string
	 * @orm:Column(name="default_value", type="text")
	 */
	protected $default_value = '';


	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @orm:Column(name="updated_at",type="datetime")
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
			return $this->default_vale;
		}

		return $this->value;
	}


	/**
	 * @orm:PrePersist
	 * @orm:PreUpdate
	 */
	public function _resetValueIfDefault()
	{
		if ($this->value == $this->default_value) {
			$this->value = null;
		}
	}

	/**
	 * @orm:PrePersist
	 * @orm:PreUpdate
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

	/** @orm:PrePersist */
	public function _incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @orm:PreUpdate */
	public function _incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}