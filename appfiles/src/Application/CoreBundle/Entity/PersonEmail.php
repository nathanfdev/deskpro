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
 * Email addresses attached to a person. This is a separate entity because emails are
 * roughly tied to identity (ie local login uses email as identity), and are integral
 * in many cases (notifications etc).
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="person_emails")
 */
class PersonEmail extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * @var Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;


	/**
	 * The email address
	 *
	 * @var string
	 * @Column(name="email", type="string", length=255)
	 */
	protected $email;


	/**
	 * @var bool
	 * @Column(name="is_validated", type="boolean")
	 */
	protected $is_validated = false;


	/**
	 * A comment or description of the email address. For example, "work" or "home."
	 *
	 * @var string
	 * @Column(name="comment", type="text")
	 */
	protected $comment = '';


	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	/**
	 * @var \DateTime
	 * @Column(name="validated_at",type="datetime", nullable=true)
	 */
	protected $validated_at = null;



	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = new \DateTime();
		if ($this->is_validated AND !$this->validated_at) {
			$this->validated_at = new \DateTime();
		}
	}

	/** @PreSave */
	public function incValidatedAt()
	{
		if ($this->is_validated AND !$this->validated_at) {
			$this->validated_at = new \DateTime();
		}
	}
}