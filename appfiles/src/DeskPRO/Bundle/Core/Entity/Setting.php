<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Bundle\Core\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @Entity
 * @Table(name="settings")
 */
class Setting extends \DeskPRO\Bundle\Core\Entity\Entity
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The name of the setting
	 *
	 * @var string
	 * @Column(name="name", type="string", length=255)
	 */
	protected $name = null;


	/**
	 * Settings can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.url, the group is 'deskpro'
	 *
	 * @var string
	 * @Index
	 * @Column(name="group", type="string", length=255, nullable=true, default=null)
	 */
	protected $group;


	/**
	 * The value of a setting
	 *
	 * @var string
	 * @Column(name="group", type="text", nullable=true, default=null)
	 */
	protected $value;


	/**
	 * The default value set by DeskPRO.
	 *
	 * @var string
	 * @Column(name="group", type="text", default="")
	 */
	protected $default_value;


	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @Column(name="updated_at",type="datetime")
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
	 * @PrePersist
	 * @PreUpdate
	 */
	public function _resetValueIfDefault()
	{
		if ($this->value == $this->default_value) {
			$this->value = null;
		}
	}

	/**
	 * @PrePersist
	 * @PreUpdate
	 */
	public function _resetGroupFromName()
	{
		$dotpos = strpos($this->name, '.');
		if ($dotpos) {
			$this->group = substr($this->name, 0, $dotpos);
		} else {
			$this->group = null;
		}
	}

	/** @PrePersist */
	public function _incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @PreUpdate */
	public function _incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}