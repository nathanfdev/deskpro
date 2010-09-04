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

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Templates used in the system
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="phrases")
 */
class Phrase extends \DeskPRO\Domain\DomainObject
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
	 * The language ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="language_id", type="integer")
	 */
	protected $language_id = null;


	/**
	 * The language this phrase belongs to
	 *
	 * @var Style
	 * @OneToOne(targetEntity="Language")
	 * @JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language;


	/**
	 * The name of the phrase
	 *
	 * @var string
	 * @Column(name="name", type="string", length=255)
	 */
	protected $name = null;


	/**
	 * Phrases can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.profile, the group is 'deskpro'
	 *
	 * @var string
	 * @Index
	 * @Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;


	/**
	 * @var string
	 * @Column(name="phrase", type="text")
	 */
	protected $phrase;


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
	 * @PrePersist
	 * @PreUpdate
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

	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}