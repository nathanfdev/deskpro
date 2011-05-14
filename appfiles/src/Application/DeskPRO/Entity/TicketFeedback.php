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

/**
 * Feedback left on tickets
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketFeedback")
 * @orm:Table(name="ticket_feedback")
 */
class TicketFeedback extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @orm:ManyToOne(targetEntity="TicketMessage")
	 * @orm:JoinColumn(name="message_id", referencedColumnName="id")
	 */
	protected $ticket_message = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="rating", type="integer")
	 */
	protected $rating;

	/**
	 * @var string
	 * @orm:Column(name="message", type="text")
	 */
	protected $message = '';

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
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