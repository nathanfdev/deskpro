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
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="people_emails")
 */
class PersonEmail extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The person ID
	 *
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var Application\CoreBundle\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * The email address
	 *
	 * @var string
	 * @orm:Column(name="email", type="string", length=255)
	 */
	protected $email;

	/**
	 * @var bool
	 * @orm:Column(name="is_validated", type="boolean")
	 */
	protected $is_validated = false;

	/**
	 * A comment or description of the email address. For example, "work" or "home."
	 *
	 * @var string
	 * @orm:Column(name="comment", type="text", length=100)
	 */
	protected $comment = '';

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_validated",type="datetime", nullable=true)
	 */
	protected $date_validated = null;



	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->date_created = new \DateTime();
		if ($this->is_validated AND !$this->date_validated) {
			$this->date_validated = new \DateTime();
		}
	}

	/** @PreSave */
	public function incValidatedAt()
	{
		if ($this->is_validated AND !$this->date_validated) {
			$this->date_validated = new \DateTime();
		}
	}
}