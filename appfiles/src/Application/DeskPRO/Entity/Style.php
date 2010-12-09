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

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="styles")
 */
class Style extends \Application\DeskPRO\Domain\DomainObject
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
	 * The parent style ID. All styles at least descened from 1, the default.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="parent_id", type="integer")
	 */
	protected $parent_id = null;


	/**
	 * @var Style
	 * @orm:OneToOne(targetEntity="Style")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;


	/**
	 * Title of the style
	 *
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;


	/**
	 * A note or description about the style
	 *
	 * @var string
	 * @orm:Column(name="note", type="text")
	 */
	protected $note;


	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	public function setParentId($parent_id)
	{
		$this->parent_id = $parent_id;

		// TODO: Cache parents hierarchy later so template fetching is easier
		$this->_parent_has_changed = true;
	}


	/** @orm:PrePersist */
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
	}
}