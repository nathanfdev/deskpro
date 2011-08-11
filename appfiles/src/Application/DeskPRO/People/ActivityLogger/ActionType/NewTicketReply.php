<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Arrays;

class NewTicketReply extends ActionTypeAbstract
{
	protected $ticket_message;


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param \Application\DeskPRO\Entity\TicketMessage $ticket_message
	 */
	public function __construct(Person $person, TicketMessage $ticket_message)
	{
		$this->person = $person;
		$this->ticket_message = $ticket_message;
	}


	/**
	 * Get a plain array of details that'll be stored in the databaes
	 * @return array
	 */
	public function getDetails()
	{
		return array(
			'ticket_id'  => $this->ticket_message->ticket['id'],
			'message_id' => $this->ticket_message['id'],
			'subject' => $this->ticket_message->ticket['subject'],
			'message'    => $this->ticket_message->getMessageText()
		);
	}
}