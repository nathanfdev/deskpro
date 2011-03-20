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
 * A note is a private note added by an agent to a persons account.
 *
 * @orm:Entity
 * @orm:Table(name="organization_notes")
 */
class OrganizationNote extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The org the note is attached to.
	 * 
	 * @var \Application\DeskPRO\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization;

	/**
	 * The agent that added the note
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="agent_id", referencedColumnName="id")
	 */
	protected $agent;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The note contents
	 *
	 * @var string
	 * @orm:Column(name="note", type="string")
	 */
	protected $note;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function getNoteHtml()
	{
		return nl2br(htmlspecialchars($this->note), true);
	}
}