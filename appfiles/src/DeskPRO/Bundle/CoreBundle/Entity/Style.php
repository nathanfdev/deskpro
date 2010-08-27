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

namespace DeskPRO\Bundle\CoreBundle\Entity;

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="styles")
 */
class Style extends \DeskPRO\Bundle\CoreBundle\Entity\Entity
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
     * @OneToMany(targetEntity="Style", mappedBy="parent")
     */
    protected $children;

	/**
	 * @var Style
	 * @ManyToOne(targetEntity="Style", inversedBy="children")
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
	protected $note = '';


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


	/** @PrePersist */
	public function _incCreatedAt()
	{
		$this->created_at = new \DateTime();
	}

	/** @PreUpdate @PrePersist */
	public function setDefaultValues()
	{
		if (!$this->note) $this->note = '';
	}


	/**
	 * Load validators for use with the validator service.
	 * 
	 * @param ClassMetadata $metadata
	 */
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('title', new Constraints\NotBlank());
	}
}