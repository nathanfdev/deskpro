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

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="languages")
 */
class Language extends \DeskPRO\Domain\DomainObject
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
	 * @var Style
	 * @OneToOne(targetEntity="Language")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;

	
	/**
	 * Locale associated with this language.
	 *
	 * @var string
	 * @Column(name="locale", type="string", length=20)
	 */
	protected $locale = 'en_US';


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

	/**
	 * Load validators for use with the validator service.
	 *
	 * @param ClassMetadata $metadata
	 */
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('title', new Constraints\NotBlank());
		$metadata->addPropertyConstraint('locale', new Constraints\NotBlank());
	}
}