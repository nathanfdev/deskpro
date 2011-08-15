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

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A note is a private note added by an agent to a persons account.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="organization_notes")
 */
class OrganizationNote extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The org the note is attached to.
	 * 
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $organization;

	/**
	 * The agent that added the note
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $agent;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The note contents
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="note", type="string")
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