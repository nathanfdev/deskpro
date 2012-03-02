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
 * Feedback left on tickets
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketFeedback")
 * @ORM_Mapping\Table(name="ticket_feedback")
 */
class TicketFeedback extends \Application\DeskPRO\Domain\DomainObject
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
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketMessage")
	 * @ORM_Mapping\JoinColumn(name="message_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket_message = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="rating", type="integer")
	 */
	protected $rating;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="message", type="text")
	 */
	protected $message = '';

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	protected $_is_new = false;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->_is_new = true;
	}

	/**
	 * Is this is a new record? (ie not persisted, or persisted this request)
	 * @return bool
	 */
	public function isNewFeedback()
	{
		return $this->_is_new;
	}

	public function getPersonId()
	{
		return $this->person['id'];
	}

	public function setPersonId($id)
	{
		$person = App::getOrm()->getRepository('DeskPRO:Person')->find($id);
		$this['person'] = $person;
	}

	public function getTicketId()
	{
		return $this->ticket['id'];
	}

	public function setTicketId($id)
	{
		$ticket = App::getOrm()->getRepository('DeskPRO:Ticket')->find($id);
		$this['ticket'] = $ticket;
	}

	public function getMessageId()
	{
		return $this->ticket_message->id;
	}

	public function setRating($rating)
	{
		if ($rating > 0) {
			$this->rating = 1;
		} else {
			$this->rating = -1;
		}
	}

	public function rateUp()
	{
		$this->setRating(1);
	}

	public function rateDown()
	{
		return $this->setRating(-1);
	}
}
