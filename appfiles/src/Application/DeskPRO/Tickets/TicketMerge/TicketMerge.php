<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketMerge;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketDeleted;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Arrays;

/**
 * Handles merging of one ticket into the other
 */
class TicketMerge implements \Application\DeskPRO\People\PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $other_ticket;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(Person $person_performer, Ticket $ticket, Ticket $other_ticket)
	{
		$this->em = App::getOrm();
		
		$this->ticket = $ticket;
		$this->other_ticket = $other_ticket;
		$this->setPersonContext($person_performer);

		if ($ticket == $other_ticket) {
			throw new \InvalidArgumentException("You cannot merge a ticket with itself");
		}
	}

	public function setPersonContext(Person $person)
	{
		$this->person = $person;
	}

	public function checkPersonPermission()
	{
		// todo
		return true;
	}

	public function merge()
	{
		if (!$this->checkPersonPermission()) {
			throw new \DomainException('User does not have permission to merge these tickets');
		}

		$this->em->beginTransaction();

		try {

			$this->mergeMessages();
			$this->mergeAttachments();
			$this->mergeParticipants();
			$this->mergeLogs();
			$this->mergeMisc();

			// dont add logs for new messages etc
			$this->ticket->resetTicketLogger();

			$prop_agent = new Property\Agent($this->ticket, $this->other_ticket);
			$prop_agent->setStrategy(Property\Agent::STRATEGY_RIGHT);
			$prop_agent->merge();

			$prop_person = new Property\Person($this->ticket, $this->other_ticket);
			$prop_person->setStrategy(Property\Person::STRATEGY_RIGHT);
			$prop_person->merge();

			$standard_prop_names = array(
				'agent',
				'agent_team',
				'person',
				'person_email',
				'department',
				'category',
				'product',
				'workflow',
				'organization',
				'status',
				'hidden_status',
				'subject'
			);
			foreach ($standard_prop_names as $prop_name) {
				$prop_standard = new Property\StandardProperty($this->ticket, $this->other_ticket);
				$prop_standard->setProperty($prop_name);
				$prop_standard->setStrategy(Property\StandardProperty::STRATEGY_RIGHT);
				$prop_standard->merge();
			}

			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			foreach ($ticket_field_defs as $f) {
				$prop_field = new Property\CustomField($this->ticket, $this->other_ticket);
				$prop_field->setField($f);
				$prop_field->setStrategy(Property\StandardProperty::STRATEGY_RIGHT);
				$prop_field->merge();
			}

			$ticket_del = new TicketDeleted();
			$ticket_del->ticket_id = $this->other_ticket['id'];
			$ticket_del->new_ticket_id = $this->ticket['id'];
			$ticket_del->by_person = $this->person;
			$ticket_del->reason = "Merge into " . $this->ticket['id'];
			$this->em->persist($ticket_del);

			$this->em->persist($this->ticket);
			$this->em->remove($this->other_ticket);

			$this->other_ticket->resetTicketLogger();
			$this->ticket->getTicketLogger()->recordExtra('ticket_merge', array('other_ticket' => $this->other_ticket));

			$this->em->flush();
		} catch (\Exception $e) {
			$this->em->rollback();

			throw $e;
		}
		
		$this->em->commit();

		return true;
	}


	protected function mergeMessages()
	{
		foreach ($this->other_ticket->messages as $message) {
			$this->other_ticket->messages->removeElement($message);
			$this->ticket->addMessage($message);
		}
	}

	protected function mergeLogs()
	{
		App::getDb()->executeUpdate("
			UPDATE tickets_logs
			SET ticket_id = ?
			WHERE ticket_id = ?
		", array($this->ticket['id'], $this->other_ticket['id']));
	}

	protected function mergeAttachments()
	{
		foreach ($this->other_ticket->attachments as $attach) {
			$this->other_ticket->attachments->removeElement($attach);
			$this->ticket->addAttachment($attach);
		}
	}

	protected function mergeParticipants()
	{
		foreach ($this->other_ticket->participants as $part) {
			$this->ticket->addParticipantPerson($part->person);
		}
	}

	protected function mergeMisc()
	{
		// Flags
		App::getDb()->executeUpdate("
			UPDATE tickets_flagged
			SET ticket_id = ?
			WHERE ticket_id = ?
		", array($this->ticket['id'], $this->other_ticket['id']));
		App::getDb()->delete('tickets_flagged', array('ticket_id' => $this->other_ticket['id']));

		// Pending articles
		App::getDb()->executeUpdate("
			UPDATE article_pending_create
			SET ticket_id = ?
			WHERE ticket_id = ?
		", array($this->ticket['id'], $this->other_ticket['id']));
		App::getDb()->delete('article_pending_create', array('ticket_id' => $this->other_ticket['id']));

		// Pending articles
		App::getDb()->executeUpdate("
			UPDATE labels_tickets
			SET ticket_id = ?
			WHERE ticket_id = ?
		", array($this->ticket['id'], $this->other_ticket['id']));
		App::getDb()->delete('labels_tickets', array('ticket_id' => $this->other_ticket['id']));
	}
}