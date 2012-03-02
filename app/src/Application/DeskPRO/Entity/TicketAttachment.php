<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Ticket attachments
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketAttachment")
 * @ORM_Mapping\Table(name="tickets_attachments")
 */
class TicketAttachment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @ORM_Mapping\ManyToOne(targetEntity="Ticket")
	 * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket;

	/**
	 * Who created the attachment
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketMessage", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="message_id", referencedColumnName="id")
	 */
	protected $message = null;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent_note", type="boolean")
	 */
	protected $is_agent_note = false;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}


	/**
	 * @param $message
	 */
	public function setMessage($message)
	{
		$this->setModelField('message', $message);

		// Automatically set the is_agent_note field
		if ($message && $message->is_agent_note) {
			$this->is_agent_note = true;
		}
	}
}
