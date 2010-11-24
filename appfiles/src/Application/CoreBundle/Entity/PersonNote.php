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
 * A note is a private note added by an agent to a persons account.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="people_notes")
 */
class PersonNote extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * The person the note is attached to.
	 * 
	 * @var \Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var int
	 * @Column(name="agent_id", type="integer")
	 */
	protected $agent_id;

	/**
	 * The agent that added the note
	 * 
	 * @var \Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person")
	 * @JoinColumn(name="agent_id", referencedColumnName="id")
	 */
	protected $agent;

	/**
	 * @var \DateTime
	 * @Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The note contents
	 *
	 * @var string
	 * @Column(name="note", type="string")
	 */
	protected $note;

	public function getNoteHtml()
	{
		return nl2br(htmlspecialchars($this->note), true);
	}

	/** @PrePersist */
	public function _prePersist()
	{
		$this->date_created = new \DateTime();
	}
}