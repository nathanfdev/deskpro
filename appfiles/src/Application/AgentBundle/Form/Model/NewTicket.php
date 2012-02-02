<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Organization;

class NewTicket
{
	public $person;

	public $subject;
	public $notify_template = '';
	public $message;
	public $department_id;
	public $status = 'awaiting_agent';
	public $agent_id;
	public $agent_team_id;
	public $category_id = 0;
	public $priority_id = 0;
	public $workflow_id = 0;
	public $product_id = 0;

	public $new_parts = '';
	public $attach = array();

	protected $_ticket;
	protected $_person_context;

	public function __construct(Person $person_context)
	{
		$this->person = new NewTicketPerson();
		$this->_person_context = $person_context;
	}

	public function save()
	{
		$em = App::getOrm();
		$em->beginTransaction();

		#------------------------------
		# The user owner
		#------------------------------

		if ($this->person->id) {
			$person = $em->find('DeskPRO:Person', $this->person->id);
		} else {
			$person = $em->getRepository('DeskPRO:Person')->findOneByEmail($this->person->email_address);
		}

		if (!$person) {
			$person = new Person();
			$person->addEmailAddressString($this->person->email_address);
		}

		if ($this->person->organization) {
			$org = $em->getRepository('DeskPRO:Organization')->findOneByName($this->person->organization);
			if (!$org) {
				$org = new Organization();
				$org['name'] = $this->person->organization;
				$em->persist($org);
			}
			$person->organization = $org;

			if ($this->person->organization_position) {
				$person['organization_position'] = $this->person->organization_position;
			}
		}

		if (!$person->name && $this->person->name) {
			$person->name = $this->person->name;
		}

		$em->persist($person);
		$em->flush();

		#------------------------------
		# Ticket
		#------------------------------

		// Ticket props
		$ticket = new Ticket();
		$ticket['creation_system'] = Ticket::CREATED_WEB_AGENT;

		$email = $person->findEmailAddress($this->person->email_address);
		if ($email) {
			$ticket->person_email = $email;
		}

		$standard = array(
			'subject', 'status', 'agent_id', 'agent_team_id',
			'department_id', 'category_id', 'priority_id', 'workflow_id',
			'product_id', 'notify_template'
		);
		foreach ($standard as $k) {
			$ticket[$k] = $this->$k;
		}

		if (!$ticket['notify_template']) {
			$ticket['notify_template'] = '';
		}


		$ticket->person = $person;


		#------------------------------
		# Message
		#------------------------------

		// Message
		$message = new TicketMessage();
		$message->person = $this->_person_context;
		$message->setVisitorFromRequest();
		$message->setMessageText($this->message);

		// Message Attachments
		foreach ($this->attach as $blob_id) {

			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->_person_context;

			$message->addAttachment($attach);
		}

		$ticket->addMessage($message);


		if ($dupe_ticket = App::getEntityRepository('DeskPRO:Ticket')->checkDupeTicket($ticket)) {
			$em->rollback();
			$this->_ticket = $dupe_ticket;
			return;
		}

		$em->persist($ticket);
		$em->flush();
		$em->persist($message);
		$em->flush();

		#------------------------------
		# Participants
		#------------------------------

		$user_parts_emails = $this->new_parts;
		$user_parts_emails = explode(',', $user_parts_emails);

		// CC'ed
		$new_parts_to_people = array();
		$email_validator = new \Orb\Validator\StringEmail();

		foreach ($user_parts_emails as $email) {
			$email = trim($email);
			if (!$email || !$email_validator->isValid($email)) {
				continue;
			}

			$cc_person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($email);
			if (!$cc_person) {
				$cc_person = Person::newContactPerson(array('email' => $email));
				$em->persist($cc_person);
			}

			$new_parts_to_people[] = $cc_person;
		}

		$em->flush();

		foreach ($new_parts_to_people as $cc_person) {
			$ticket->addParticipantPerson($cc_person);
		}

		$em->persist($ticket);
		$em->flush();
		$em->commit();

		$this->_ticket = $ticket;
	}

	public function getTicket()
	{
		return $this->_ticket;
	}
}
