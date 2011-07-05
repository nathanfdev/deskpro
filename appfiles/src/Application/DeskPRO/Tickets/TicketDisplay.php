<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

class TicketDisplay implements PersonContextInterface
{
	protected $ticket;

	protected $person_context;
	protected $person_type = 'user';

	protected $user_participants;
	protected $agent_participants;

	protected $notes;
	protected $messages;
	protected $attachments;
	protected $message_to_attach;

	public function __construct(Ticket $ticket, Person $person)
	{
		$this->ticket = $ticket;
		$this->setPersonContext($person);
	}

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
		if ($person['is_agent']) {
			$this->person_type = 'agent';
		}
	}

	public function getUserParticipants()
	{
		if ($this->user_participants !== null) return $this->user_participants;

		$this->user_participants = array();

		foreach ($this->ticket->getParticipants() as $part) {
			if (!$part->person['is_agent']) {
				$this->user_participants[] = $part;
			}
		}

		return $this->user_participants;
	}

	public function getAgentParticipants()
	{
		if ($this->agent_participants !== null) return $this->agent_participants;

		$this->agent_participants = array();

		foreach ($this->ticket->getParticipants() as $part) {
			if ($part->person['is_agent']) {
				$this->agent_participants[] = $part;
			}
		}

		return $this->agent_participants;
	}

	public function getNotes()
	{
		if ($this->notes !== null) return $this->notes;
		
		$this->getMessages();

		$this->notes = array();

		foreach ($this->messages as $message) {
			if ($message['is_agent_note']) {
				$this->notes[] = $message;
			}
		}
		
		return $this->notes;
	}

	public function getMessages()
	{
		if ($this->messages !== null) return $this->messages;

		if ($this->person_type == 'agent') {
			$this->messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages(
				$this->ticket,
				array('with_notes' => true)
			);
		} else {
			$this->messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages(
				$this->ticket,
				array('with_notes' => false)
			);
		}

		return $this->messages;
	}

	public function getAttachments()
	{
		if ($this->attachments !== null) return $this->attachments;

		$this->attachments = App::getEntityRepository('DeskPRO:TicketAttachment')->getTicketAttachments($this->ticket);

		return $this->attachments;
	}

	public function getMessagesToAttachments()
	{
		if ($this->message_to_attach !== null) return $this->message_to_attach;

		$this->getMessages();
		$this->getAttachments();

		$this->message_to_attach = array();

		foreach ($this->attachments as $attach) {
			if (!isset($this->message_to_attach[$attach['message']['id']])) {
				$this->message_to_attach[$attach['message']['id']] = array();
			}

			$this->message_to_attach[$attach['message']['id']][] = $attach['id'];
		}

		return $this->message_to_attach;
	}

	public function getDisplayArray()
	{
		return array(
			'ticket' => $this->ticket,

			'user_participants'  => $this->getUserParticipants(),
			'agent_participants' => $this->getAgentParticipants(),

			'notes' => $this->getNotes(),
			'messages' => $this->getMessages(),
			'attachments' => $this->getAttachments(),
			'message_to_attach' => $this->getMessagesToAttachments()
		);
	}
}