<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A note is a private note added by an agent to a persons account.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DealNote")
 * @ORM_Mapping\Table(name="deal_notes")
 */
class DealNote extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * The note is attached to the deal .
	 *
	 * @var \Application\DeskPRO\Entity\Deal
	 * @ORM_Mapping\ManyToOne(targetEntity="Deal")
	 * @ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $deal;

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
