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

namespace DeskPRO\Bundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @Entity
 * @Table(name="languages")
 */
class Language extends \DeskPRO\Bundle\Entity\Entity
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
	 * The parent language ID. All languages at least descened from 1, the default.
	 *
	 * @var int
	 * @Id
	 * @Column(name="parent_id", type="integer")
	 */
	protected $parent_id = null;


	/**
	 * @var Style
	 * @OneToOne(targetEntity="Language")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;


	/**
	 * Title of the style
	 *
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title;


	/**
	 * A note or description about the style
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note;


	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	public function setParentId($parent_id)
	{
		$this->parent_id = $parent_id;

		// TODO: Cache parents hierarchy later so template fetching is easier
		$this->_parent_has_changed = true;
	}


	/**
	 * @PrePersist
	 * @PreUpdate
	 */
	public function _setDefaultParentId()
	{
		if (!$this->parent_id) {
			$this->setParentId(1);
		}
	}


	/** @PrePersist */
	public function _incCreatedAt()
	{
		$this->created_at = new \DateTime();
	}
}