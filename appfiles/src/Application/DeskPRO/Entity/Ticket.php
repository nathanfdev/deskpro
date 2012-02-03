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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Ticket
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Ticket")
 * @ORM_Mapping\Table(name="tickets")
 * @ORM_Mapping\HasLifecycleCallbacks
 *
 * @property \Application\DeskPRO\Entity\Person $person
 */
class Ticket extends \Application\DeskPRO\Domain\DomainObject
{
	const CREATED_WEB_PERSON = 'web.person';
	const CREATED_WEB_AGENT = 'web.agent';
	const CREATED_GATEWAY_PERSON = 'gateway.person';

	const STATUS_AWAITING_AGENT = 'awaiting_agent';
	const STATUS_AWAITING_USER = 'awaiting_user';
	const STATUS_RESOLVED = 'resolved';
	const STATUS_CLOSED = 'closed';
	const STATUS_HIDDEN = 'hidden';

	const HIDDEN_STATUS_VALIDATING = 'validating';
	const HIDDEN_STATUS_SPAM = 'spam';
	const HIDDEN_STATUS_DELETED = 'deleted';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ref", type="string", length=25)
	 */
	protected $ref = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="auth", type="string", length=20)
	 */
	protected $auth;

	/**
	 * The language the ticket is in
	 *
	 * @var \Application\DeskPRO\Entity\Language
	 * @ORM_Mapping\ManyToOne(targetEntity="Language", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="language_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $language = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @ORM_Mapping\ManyToOne(targetEntity="Department", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="department_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $department = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $category = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketPriority
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketPriority", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="priority_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $priority = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketWorkflow
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketWorkflow", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="workflow_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $workflow = null;

	/**
	 * @var \Application\DeskPRO\Entity\Product
	 * @ORM_Mapping\ManyToOne(targetEntity="Product", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="product_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $product = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 * @ORM_Mapping\ManyToOne(targetEntity="PersonEmail", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_email_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person_email = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmailValidating
	 * @ORM_Mapping\ManyToOne(targetEntity="PersonEmailValidating", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_email_validating_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person_email_validating = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $agent = null;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @ORM_Mapping\ManyToOne(targetEntity="AgentTeam", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $agent_team = null;

	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $organization = null;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="TicketAttachment", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $attachments;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="TicketAccessCode", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $access_codes;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="TicketMessage", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $messages;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="CustomDataTicket", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelTicket", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * The gateway this ticket originated from
	 *
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 * @ORM_Mapping\ManyToOne(targetEntity="EmailGateway")
	 * @ORM_Mapping\JoinColumn(name="email_gateway_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $email_gateway = null;

	/**
	 * The gateway email address the ticket matched
	 *
	 * @var \Application\DeskPRO\Entity\EmailGatewayAddress
	 * @ORM_Mapping\ManyToOne(targetEntity="EmailGatewayAddress")
	 * @ORM_Mapping\JoinColumn(name="email_gateway_address_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $email_gateway_address = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="notify_template", type="string", length=200)
	 */
	protected $notify_template = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ticket_hash", type="string", length=40)
	 */
	protected $ticket_hash;

	/**
	 * @!TODO Make this an enum type
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @!TODO Make this an enum type
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="hidden_status", type="string", length=15, nullable=true)
	 */
	protected $hidden_status = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="validating", type="string", length=35, nullable=true)
	 */
	protected $validating = null;

	/**
	 * Is the ticket on hold?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_hold", type="boolean")
	 */
	protected $is_hold = false;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="urgency", type="integer")
	 */
	protected $urgency = 1;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_resolved",type="datetime",nullable=true)
	 */
	protected $date_resolved = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_closed",type="datetime",nullable=true)
	 */
	protected $date_closed = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_first_agent_assign", type="datetime", nullable=true)
	 */
	protected $date_first_agent_assign = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_first_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_first_agent_reply = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_last_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_last_agent_reply = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_last_user_reply",type="datetime",nullable=true)
	 */
	protected $date_last_user_reply = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_agent_waiting",type="datetime",nullable=true)
	 */
	protected $date_agent_waiting = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_user_waiting",type="datetime",nullable=true)
	 */
	protected $date_user_waiting = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="total_user_waiting", type="integer")
	 */
	protected $total_user_waiting = 0;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="total_to_first_reply", type="integer")
	 */
	protected $total_to_first_reply = 0;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="locked_by_agent", referencedColumnName="id")
	 */
	protected $locked_by_agent = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_locked",type="datetime",nullable=true)
	 */
	protected $date_locked = null;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="has_attachments", type="boolean")
	 */
	protected $has_attachments = false;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="subject", type="string", length=255)
	 */
	protected $subject;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TicketParticipant", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $participants;

	/**
	 * Array cache of user participants
	 * @var array
	 * @see getUserParticipants
	 */
	protected $_user_participants;

	/**
	 * Ticket logger
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $_ticket_logger;

	/**
	 * When true the ticket log doesnt run in the post event
	 * @var bool
	 */
	protected $_no_log = false;

	protected $_label_manager = null;

	public function __construct($tracker = true)
	{
		$this->participants = new \Doctrine\Common\Collections\ArrayCollection();
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();
		$this->custom_data = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
		$this->access_codes = new \Doctrine\Common\Collections\ArrayCollection();

		$this->date_created = new \DateTime();

		$len = App::getSetting('core_tickets.ptac_auth_code_len');
		$this->auth = Strings::random($len, Strings::CHARS_KEY);

		if ($tracker) {
			$this->_initTicketLogger();
			$this->_ticket_logger->recordExtra('ticket_created', true);
		}
	}

	public function setNoLog()
	{
		$this->_no_log = true;
	}

	/**
	 * @ORM_Mapping\PostLoad
	 */
	public function _initTicketLogger()
	{
		if ($this->_ticket_logger) {
			$this->removePropertyChangedListener($this->_ticket_logger);
		}
		$ticket_logger = new \Application\DeskPRO\Tickets\TicketChangeTracker($this);
		$this->_ticket_logger = $ticket_logger;
		$this->addPropertyChangedListener($ticket_logger);
	}


	public function getUserParticipants()
	{
		if ($this->_user_participants !== null) return $this->_user_participants;

		$this->_user_participants = array();

		foreach ($this->participants as $p) {
			if (!$p['person']['is_agent']) {
				$this->_user_participants[] = $p;
			}
		}

		return $this->_user_participants;
	}

	public function getAgentParticipants()
	{
		$ret = array();
		foreach ($this->participants as $p) {
			if ($p->person['is_agent']) {
				$ret[] = $p;
			}
		}

		return $ret;
	}


	/**
	 * Given an array of agents, sync the current parts with those in the array.
	 * So remove ones that aren't in it, or add new ones
	 *
	 * @param array $parts
	 * @return void
	 */
	public function setAgentParticipants(array $agents)
	{
		$current_agent_ids = array();
		foreach ($this->participants as $p) {
			if ($p->person->is_agent) {
				$current_agent_ids[] = $p->person->id;
			}
		}

		$got_agent_ids = array();
		foreach ($agents as $p) {
			$got_agent_ids[] = $p->id;
		}
		foreach ($got_agent_ids as $id) {
			$this->addParticipantPerson($id);
		}

		$remove_agent_ids = array_diff($current_agent_ids, $got_agent_ids);
		foreach ($remove_agent_ids as $id) {
			$this->removeParticipantPerson($id);
		}
	}


	/**
	 * Try to find a user that is a part of this tikcet based on
	 * their email address.
	 * @param $email_address
	 * @return Person
	 */
	public function findUserByEmail($email_address)
	{
		// The author
		if ($this->person->findEmailAddress($email_address)) {
			return $this->person;

		// Any of the participants
		} else {
			foreach ($this->getUserParticipants() as $part) {
				if ($part->person->findEmailAddress($email_address)) {
					return $part->person;
				}
			}
		}

		return null;
	}


	/**
	 * Modify urgency by $mod, which can be positive or negative.
	 *
	 * @param int $mod
	 * @param bool $reset_on_reply True to reset this urgency after the next reply
	 */
	public function modifyUrgency($mod, $reset_on_reply = false)
	{
		$old_u = $this->urgency;
		$new_u = \Orb\Util\Numbers::bound($old_u + $mod, 1, 100);

		if ($old_u != $new_u) {
			$this->urgency = $new_u;

			$this->_onPropertyChanged('urgency', $old_u, $new_u);
			if ($reset_on_reply) {
				$real_diff = $old_u - $new_u;// real mod, taking into account bound()
				$this->getTicketLogger()->recordExtra('urgency_reset_reply', $real_diff);
			}
		}
	}


	/**
	 * Set the urgency to a specific value
	 *
	 * @param int $set
	 */
	public function setUrgency($set)
	{
		$old_u = $this->urgency;
		$new_u = \Orb\Util\Numbers::bound($set, 1, 100);

		if ($old_u != $new_u) {
			$this->urgency = $new_u;
			$this->_onPropertyChanged('urgency', $old_u, $new_u);
		}
	}


	/**
	 * Get a summary line. This is the ticket subject, and if $max_len allows,
	 * the first characters of the first message.
	 *
	 * This is usually used in templates where a line or two are displayed in a list
	 *
	 * @return string
	 */
	public function getSummaryLine($max_len = 200, $include_subject = false)
	{
		if ($include_subject) {
			$summary = $this->subject;
		} else {
			$summary = '';
		}

		if (strlen($summary) < $max_len) {
			if ($include_subject) {
				$summary .= '. ';
			}

			// !TODO
			// THis is used in the result listings, so this needs
			// to be made more efficient than running a query per message

			$first_message = App::getEntityRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($this);
			if ($first_message) {
				$summary .= Strings::removeLineBreaks($first_message->getMessageText());
			}
		}

		if (strlen($summary) > $max_len) {
			$summary = substr($summary, 0, $max_len);
		}

		return $summary;
	}


	/**
	 * Get a simple array of person ID's of participants.
	 *
	 * @return array
	 */
	public function getParticipantPeopleIds()
	{
		$ids = array();
		foreach ($this->getParticipants() as $p) {
			$ids[] = $p['person']['id'];
		}

		return $ids;
	}

	public function getParticipants()
	{
		/* Temp workaround for Doctrine bug not filling $this->participants */
		$participants = APp::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			WHERE p.ticket = ?1
		")->setParameter(1, $this->id)->execute();

		return $participants;
	}

	public function getRawParticipants()
	{
		return $this->participants;
	}

	public function setRawParticipants($parts)
	{
		$this->participants = $parts;
	}



	/**
	 * Check if a person ID or a person object is current a participant.
	 *
	 * @param  $person_or_id
	 * @return bool
	 */
	public function hasParticipantPerson($person_or_id)
	{
		$person_id = $person_or_id;
		if ($person_or_id instanceof Person) {
			$person_id = $person_or_id['id'];
		}

		foreach ($this->getParticipants() as $p) {
			if ($p['person']['id'] == $person_id) {
				return $p;
			}
		}

		return false;
	}



	/**
	 * Add a participant
	 *
	 * @param $person_or_id
	 * @return TicketParticipant
	 */
	public function addParticipantPerson($person_or_id)
	{
		$person = $person_or_id;
		if (!($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		if (!$person) {
			return null;
		}

		if ($ticket_part = $this->hasParticipantPerson($person)) {
			return $ticket_part;
		}

		$ticket_part = new TicketParticipant();
		$ticket_part['person'] = $person;
		$ticket_part['ticket'] = $this;
		$this->participants->add($ticket_part);

		if ($this->getTicketLogger()) {
			$this->getTicketLogger()->recordMultiPropertyChanged('participants', null, $person);
		}

		if ($this->_user_participants !== null AND !$person['is_agent']) {
			$this->_user_participants[] = $ticket_part;
		}

		return $ticket_part;
	}



	/**
	 * Remove a participant
	 *
	 * @param  $person_or_id
	 * @return null
	 */
	public function removeParticipantPerson($person_or_id)
	{
		$person = $person_or_id;
		if (!($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		if (!$person) {
			return null;
		}

		foreach ($this->participants as $k => $p) {
			if ($p['person']['id'] == $person['id']) {
				if ($this->getTicketLogger()) $this->getTicketLogger()->recordMultiPropertyChanged('participants', $p['person'], null);
				$this->participants->remove($k);
				return $p;
			}
		}

		return null;
	}

	public function addParticipant(TicketParticipant $part)
	{
		$part->ticket = $this;
		$this->participants->add($part);
	}


	/**
	 * Set agent participants. Agents are added/removed so that
	 * all participants on the ticket are in the array.
	 *
	 * @param array $set_agent_ids
	 * @return void
	 */
	public function setParticipantAgentIds(array $set_agent_ids)
	{
		$got_agent_ids = array();
		$remove_ks = array();

		/*
		 * Bug in Doctrine: $this->participants only ever has 1 record,
		 * so we're fetching them manually
		 */

		$participants = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			WHERE p.ticket = ?1
		")->setParameter(1, $this)->execute();

		foreach ($participants as $k => $part) {
			if (!$part->person['is_agent']) {
				continue;
			}

			if (!in_array($part->person['id'], $set_agent_ids)) {
				$remove_ks[] = $k;
			} else {
				$got_agent_ids[] = $part->person['id'];
			}
		}

		foreach ($remove_ks as $k) {
			App::getOrm()->remove($participants[$k]);
			if ($this->getTicketLogger()) $this->getTicketLogger()->recordMultiPropertyChanged('participants', $participants[$k], null);
		}

		$new_agent_ids = array_diff($set_agent_ids, $got_agent_ids);

		if ($new_agent_ids) {
			foreach ($new_agent_ids as $agent_id) {
				$part = new \Application\DeskPRO\Entity\TicketParticipant();
				$part['person_id'] = $agent_id;

				$this->addParticipant($part);
				if ($this->getTicketLogger()) $this->getTicketLogger()->recordMultiPropertyChanged('participants', null, $participants[$k]);
			}
		}
	}


	/**
	 * Set user participants.
	 *
	 * If item in $set_user_ids is an array, its expected to be
	 * array(person_id, person_email_id)
	 *
	 * @param array $set_agent_ids
	 * @return void
	 */
	public function setParticipantUserIds(array $set_user_ids)
	{
		$got_user_ids = array();

		$set_user_ids_info = array();
		foreach ($set_user_ids as $id) {
			if (is_array($id)) {
				$set_user_ids_info[$id[0]] = array($id[0], $id[1]);
			} else {
				$set_user_ids_info[$id] = array($id, null);
			}
		}

		$set_user_ids = array_keys($set_user_ids_info);

		$participants = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			WHERE p.ticket = ?1
		")->setParameter(1, $this)->execute();

		foreach ($participants as $k => $part) {
			if ($part->person['is_agent']) continue;

			if (!isset($set_user_ids_info[$part->person['id']])) {
				//$this->participants->remove($k);
				App::getOrm()->remove($participants[$k]);
			} else {
				$got_user_ids[] = $part->person['id'];

				$info = $set_user_ids_info[$part->person['id']];
				if ($info[1] AND $info[1] != $part->person_email['id']) {
					$part->setPersonEmailId($info[1]);
				}
			}
		}

		$new_user_ids = array_diff($set_user_ids, $got_user_ids);

		if ($new_user_ids) {
			foreach ($new_user_ids as $person_id) {
				$part = new \Application\DeskPRO\Entity\TicketParticipant();
				$part['person_id'] = $person_id;

				$info = $set_user_ids_info[$part->person['id']];
				if ($info[1]) {
					$part->setPersonEmailId($info[1]);
				}

				$this->addParticipant($part);
			}
		}
	}


	/**
	 * Add a message to this ticket.
	 *
	 * @param TicketMessage $message
	 */
	public function addMessage(TicketMessage $message)
	{
		$this->messages->add($message);
		$message->ticket = $this;

		$now = new \DateTime();
		if ($message->person['is_agent']) {
			if (!($this->date_last_agent_reply || $this->date_last_agent_reply < $now)) {
				$this['date_last_agent_reply'] = $now;
			}

			if (!$this->date_first_agent_reply) {
				$this['date_first_agent_reply'] = $now;
			}
		} else {
			if (!($this->date_last_user_reply || $this->date_last_user_reply < $now)) {
				$this['date_last_user_reply'] = $now;
			}

			$this->setDateUserWaiting($now);
		}

		$this->_onPropertyChanged('messages', null, $message);
	}


	/**
	 * Add a ticket attachment
	 *
	 * @param TicketAttachment $attach
	 * @return void
	 */
	public function addAttachment(TicketAttachment $attach)
	{
		$attach->ticket = $this;
		$this->attachments->add($attach);
	}


	/**
	 * Find an existing data record for a field id.
	 *
	 * @param int $field_id
	 * @return CustomDataTicket
	 */
	public function getCustomDataForField($field_id)
	{
		if ($field_id instanceof CustomDefTicket) {
			$field_id = $field_id['id'];
		}

		foreach ($this->custom_data as $data) {
			if ($data['field_id'] == $field_id) {
				return $data;
			}
		}

		return null;
	}


	/**
	 * Gets a display array for a specific field
	 * @param $field_id
	 * @return array|mixed|null
	 */
	public function getCustomFieldDisplayArray($field_id)
	{
		$data = $this->getCustomDataForField($field_id);
		if (!$data) {
			return null;
		}

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(array($data), $ticket_field_defs);

		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray(
			$ticket_field_defs,
			$ticket_data_structured
		);

		$custom_fields = array_pop($custom_fields);

		return $custom_fields;
	}



	/**
	 * Set custom field data for a particular field.
	 *
	 * @param int $field_id
	 * @param mixed $value
	 * @return mixed
	 */
	public function setCustomData($field_id, $value_type, $value)
	{
		$custom_data = $this->getCustomDataForField($field_id);
		$is_new = false;

		if (!$custom_data) {
			if ($value === null) return null;

			$is_new = true;

			$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($field_id);
			if (!$field) {
				throw new \Exception("Invalid field_id `$field_id`");
			}
			$custom_data = new CustomDataTicket();
			$custom_data['field'] = $field;
		}

		$field = $custom_data->field;
		if ($field->parent) {
			foreach ($this->custom_data as $d) {
				if ($d->field && $d->field->parent && $d->field->parent['id'] == $field->parent['id']) {
					$this->custom_data->removeElement($d);
				}
			}
		}

		$this->custom_data->removeElement($custom_data);

		if ($value === null) {
			$this->custom_data->removeElement($custom_data);
			return null;
		}

		if ($field->getTypeName() == 'choice') {

		}

		$custom_data[$value_type] = $value;

		if ($is_new) {
			$this->addCustomData($custom_data);
		}

		if ($this->id) {
			App::getEntityRepository('DeskPRO:Cache')->delete("ticket_custom_fields.{$this->id}");
		}

		return $custom_data;
	}

	public function removeCustomDataForField($field)
	{
		$parent_id = null;
		$field_id = $field['id'];
		if ($field->parent) {
			$parent_id = $field->parent['id'];
		}

		foreach ($this->custom_data as $data) {
			if ($data['field_id'] == $field_id OR $data['field_id'] == $parent_id) {
				$this->custom_data->removeElement($data);
			}
		}
	}

	/**
	 * Add a custom data item to this ticket
	 *
	 * @param CustomDataTicket $data
	 */
	public function addCustomData(CustomDataTicket $data)
	{
		$this->custom_data->add($data);
		$data['ticket'] = $this;
	}


	/**
	 * Check if this ticket has a custom field.
	 *
	 * @param $field_id
	 * @return bool
	 */
	public function hasCustomField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data->field['id'] == $field_id) {
				return true;
			}
		}

		foreach ($this->custom_data as $data) {
			if ($data->field->parent AND $data->field->parent['id'] == $field_id) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Render a custom field
	 *
	 * !depreciated
	 */
	public function renderCustomField($field_id, $context = 'html')
	{
		$f_def = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($field_id);

		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, array($f_def));

		$value = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
		$rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

		return $rendered;
	}


	/**
	 * Add a label
	 * @param \Application\DeskPRO\Entity\LabelTicket $label
	 */
	public function addLabel(LabelTicket $label)
	{
		$label['ticket'] = $this;
		$this->labels->add($label);
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

	public function setPerson(Person $person)
	{
		$this->setModelField('person', $person);

		if ($person->getRealLanguage()) {
			$this->language = $person->getRealLanguage();
		}
	}

	public function getPersonEmail()
	{
		if ($this->person_email) {
			return $this->person_email;
		} else {
			return $this->person['primary_email'];
		}
	}

	public function getPersonEmailAddress()
	{
		$email = $this->getPersonEmail();
		return $email['email'];
	}

	public function getDepartmentId()
	{
		if (!$this->department) {
			return 0;
		}

		return $this->department['id'];
	}

	public function setDepartmentId($id)
	{
		if ($id) {
			$dep = App::getOrm()->getRepository('DeskPRO:Department')->find($id);
			$this['department'] = $dep;
		} else {
			$this['department'] = null;
		}
	}

	public function setDepartment(Department $dep = null)
	{
		$old_dep = $this->department;
		$this->department = $dep;
		$this->_onPropertyChanged('department', $old_dep, $dep);
	}

	public function getCategoryId()
	{
		if (!$this->category) {
			return 0;
		}
		return $this->category['id'];
	}

	public function setCategoryId($id)
	{
		if ($id) {
			$cat = App::getOrm()->getRepository('DeskPRO:TicketCategory')->find($id);
			$this['category'] = $cat;
		} else {
			$this['category'] = null;
		}
	}

	public function getProductId()
	{
		if (!$this->product) {
			return 0;
		}
		return $this->product['id'];
	}

	public function setProductId($id)
	{
		if ($id) {
			$prod = App::getOrm()->getRepository('DeskPRO:Product')->find($id);
			$this['product'] = $prod;
		} else {
			$this['product'] = null;
		}
	}

	public function getPriorityId()
	{
		if (!$this->priority) {
			return 0;
		}

		return $this->priority['id'];
	}

	public function setPriorityId($id)
	{
		if ($id) {
			$pri = App::getOrm()->getRepository('DeskPRO:TicketPriority')->find($id);
			$this['priority'] = $pri;
		} else {
			$this['priority'] = null;
		}
	}

	public function getWorkflowId()
	{
		if (!$this->workflow) {
			return 0;
		}

		return $this->workflow['id'];
	}

	public function setWorkflowId($id)
	{
		if ($id) {
			$work = App::getOrm()->getRepository('DeskPRO:TicketWorkflow')->find($id);
			$this['workflow'] = $work;
		} else {
			$this['workflow'] = null;
		}
	}

	public function getAgentId()
	{
		if (!$this->agent) {
			return 0;
		}

		return $this->agent['id'];
	}

	public function setAgentId($id)
	{

		if ($id) {
			$agent = App::getOrm()->getRepository('DeskPRO:Person')->find($id);
			if (!$agent['is_agent']) {
				// !TODO err
			}

			$this['agent'] = $agent;
			// Do we need to update the first assign date?
			if (is_null($this->date_first_agent_assign)) {
				$this['date_first_agent_assign'] = new \DateTime();
			}

		} else {
			$this['agent'] = null;
		}
	}

	public function getAgentTeamId()
	{
		if (!$this->agent_team) {
			return 0;
		}
		return $this->agent_team['id'];
	}

	public function setAgentTeamId($id)
	{
		if ($id) {
			$agent_team = App::getOrm()->getRepository('DeskPRO:AgentTeam')->find($id);
			$this['agent_team'] = $agent_team;
		} else {
			$this['agent_team'] = null;
		}
	}

	public function getEmailGatewayId()
	{
		if (!$this->email_gateway) {
			return 0;
		}

		return $this->email_gateway['id'];
	}

	public function setEmailGatewayId($id)
	{
		if ($id) {
			$g = App::getOrm()->getRepository('DeskPRO:EmailGateway')->find($id);
			$this['email_gateway'] = $g;
		} else {
			$this['email_gateway'] = null;
		}
	}

	public function getIsAssigned()
	{
		if ($this->agent OR $this->agent_team) {
			return true;
		}

		return false;
	}


	public function getAssignedName()
	{
		if ($this->agent) {
			return $this->agent['display_name'];
		} elseif ($this->agent_team) {
			return $this->agent_team['name'];
		} else {
			return null;
		}
	}

	public function setLockedByAgentId($agent_id)
	{
		if ($agent_id) {
			$agent = App::getOrm()->getRepository('DeskPRO:Person')->find($agent_id);
			$this->setLockedByAgent($agent);
		} else {
			$this->setLockedByAgent(null);
		}
	}

	public function setLockedByAgent(Person $agent = null)
	{
		$this->locked_by_agent = $agent;
		if ($agent) {
			$this->date_locked = new \DateTime();
		} else {
			$this->date_locked = null;
		}
	}

	public function unlockTicket()
	{
		$this->setLockedByAgentId(null);
	}

	public function getIsLocked()
	{
		return $this->isLocked();
	}

	public function isLocked(Person $current_agent = null)
	{
		if (!$this->locked_by_agent) {
			return false;
		}

		$lock_timeout = date_create('-' . App::getSetting('core_tickets.lock_timeout') . ' seconds');

		// Timed out
		if ($this->date_locked < $lock_timeout) {
			return false;
		}

		if ($current_agent === null) {
			$current_agent = App::getCurrentPerson();
		}
		if ($current_agent && $this->locked_by_agent['id'] == $current_agent['id']) {
			return false;
		}

		return true;
	}

	public function getIsArchived()
	{
		if ($this->status == 'closed' OR $this->status =='resolved') {
			return true;
		}

		return false;
	}



	/**
	 * Gets messages we should be showing to the user. In other words,
	 * messages that are not private agent notes.
	 *
	 * @return array
	 */
	public function getDisplayableMessages()
	{
		$ret = array();

		foreach ($this->messages as $msg) {
			if (!$msg['is_agent_note']) {
				$ret[] = $msg;
			}
		}

		return $ret;
	}



	/**
	 * Set a flag color for this ticket for a particular perosn.
	 * $color of null or 'none' removes the flag.
	 *
	 * @param Person $person
	 * @param string $color
	 * @return TicketFlagged
	 */
	public function setFlagForPerson($person, $color = null)
	{
		if ($color == 'none') $color = null;

		$ticket_flagged = App::getOrm()->getRepository('DeskPRO:TicketFlagged')->find(array(
			'ticket_id' => $this->id,
			'person_id' => $person['id']
		));
		if (!$ticket_flagged) {

			// doesnt exist, and no color, nothing to do
			if (!$color) {
				return null;
			}

			$ticket_flagged = new TicketFlagged();
			$ticket_flagged['ticket_id'] = $this->id;
			$ticket_flagged['person_id'] = $person['id'];
		}

		if (!$color) {
			App::getOrm()->remove($ticket_flagged);
			$ticket_flagged = null;
		} else {
			App::getOrm()->persist($ticket_flagged);
			$ticket_flagged['color'] = $color;
		}

		App::getOrm()->flush();

		return $ticket_flagged;
	}

	/**
	 * Gets the urgency rounded to nearest 10. Useful in ex templates to specify a color
	 *
	 * @return int
	 */
	public function getRoundedUrgency()
	{
		return \Orb\Util\Numbers::roundToMultiple($this->urgency, 10, \Orb\Util\Numbers::ROUND_MULTIPLE_NEAR);
	}



	/**
	 * Get the deletion record if there is one
	 *
	 * @return \Application\DeskPRO\Entity\TicketDeleted
	 */
	public function getDeletionRecord()
	{
		$del = App::getOrm()->createQuery("
			SELECT d
			FROM DeskPRO:TicketDeleted d
			WHERE d.ticket_id = ?1
		")->setParameter(1, $this->id)->getOneOrNullResult();

		return $del;
	}


	public function isHidden()
	{
		return $this->getIsHidden();
	}

	public function getIsHidden()
	{
		if ($this->status == self::STATUS_HIDDEN) {
			return true;
		}

		return false;
	}

	public function isDeleted()
	{
		return $this->getIsDeleted();
	}


	/**
	 * Is this ticket deleted?
	 *
	 * @return bool
	 */
	public function getIsDeleted()
	{
		if ($this->hidden_status == self::HIDDEN_STATUS_DELETED) {
			return true;
		}

		return false;
	}


	public function getRealTotalUserWaiting()
	{
		$secs = $this->total_user_waiting;

		if ($this->date_user_waiting) {
			$secs += time() - $this->date_user_waiting->getTimestamp();
		}

		return $secs;
	}


	public function setStatus($status)
	{
		$old_status  = $this->status;

		if ($status == 'awaiting_user' && $old_status == 'awaiting_agent' && $this->date_user_waiting) {
			$this->total_user_waiting += time() - $this->date_user_waiting->getTimestamp();
			$this->date_user_waiting = null;
		} else if ($status == 'closed' && $old_status == 'awaiting_user' && $this->date_user_waiting) {
			$this->total_user_waiting += time() - $this->date_user_waiting->getTimestamp();
			$this->date_user_waiting = null;
		} else if ($status == 'awaiting_agent') {
			$this->date_user_waiting = new \DateTime();
		}

		$old_hstatus = $this->hidden_status;
		$old_status_code = "$old_status.$old_hstatus";

		$status_code = $status;
		$hstatus = null;
		if (strpos($status, '.')) {
			list($status, $hstatus) = explode('.', $status, 2);
		}

		if (!in_array($status, array(
			self::STATUS_AWAITING_AGENT,
			self::STATUS_AWAITING_USER,
			self::STATUS_CLOSED,
			self::STATUS_RESOLVED,
			self::STATUS_HIDDEN
		))) {
			throw new \InvalidArgumentException("Invalid status `$status`");
		}

		if ($hstatus && !in_array($hstatus, array(
			self::HIDDEN_STATUS_DELETED,
			self::HIDDEN_STATUS_SPAM,
			self::HIDDEN_STATUS_VALIDATING
		))) {
			throw new \InvalidArgumentException("Invalid hidden status `$hstatus`");
		}

		$this->setModelField('status', $status);
		$this->setModelField('hidden_status', $hstatus);

		if ($old_status_code == 'hidden.deleted' || $status_code != 'hidden.deleted') {
			$this->undeleteTicket();
		}

		if ($this->is_hold && $status != self::STATUS_AWAITING_AGENT) {
			$this->setModelField('is_hold', false);
		}
	}

	public function setHiddenStatus($hstatus)
	{
		if (!$hstatus) {
			if ($this->status == 'hidden') {
				$this->setStatus('awaiting_agent');
			}
		} else {
			$this->setStatus('hidden.' . $hstatus);
		}
	}

	public function getStatusCode()
	{
		return $this->status . ($this->hidden_status ? ".{$this->hidden_status}" : '');
	}


	/**
	 * Undelete a ticket.
	 *
	 * This will set the status to 'awaiting_agent' if it wasn't changed before.
	 */
	public function undeleteTicket()
	{
		$del = $this->getDeletionRecord();
		if (!$del) {
			return;
		}

		if ($this->status == 'hidden') {
			$this->status = self::STATUS_AWAITING_AGENT;
		}

		App::getOrm()->remove($del);
		App::getOrm()->persist($this);
	}



	/**
	 * Soft-delete a ticket
	 *
	 * @param null $person
	 * @param string $reason
	 * @return void
	 */
	public function deleteTicket($person = null, $reason = '')
	{
		$del = $this->getDeletionRecord();
		if (!$del) {
			$del = new TicketDeleted();
		}

		$del['ticket_id']     = $this->id;
		$del['by_person']     = $person;
		$del['new_ticket_id'] = 0;
		$del['reason']        = $reason;

		$this->setStatus('hidden.deleted');

		App::getOrm()->persist($del);
		App::getOrm()->flush($del);
		App::getOrm()->persist($this);
	}



	/**
	 * Add an access code for a person
	 *
	 * @param PersonEmail $email
	 */
	public function addAccessCodeForPerson(Person $person)
	{
		if ($tac = $this->findAccessCodeForPerson($person)) {
			return $tac;
		}

		$tac = new TicketAccessCode();
		$tac['ticket'] = $this;
		$tac['person'] = $person;
		$this->access_codes->add($tac);
	}



	/**
	 * Find the access code for a person if it exists
	 *
	 * @return TicketAccessCode
	 */
	public function findAccessCodeForPerson(Person $person)
	{
		foreach ($this->access_codes as $tac) {
			if ($tac->person = $person) {
				return $tac;
			}
		}

		return null;
	}



	/**
	 * Find an access code
	 *
	 * @return TicketAccessCode
	 */
	public function findAccessCode($auth)
	{
		foreach ($this->access_codes as $tac) {
			if ($tac['auth'] == $auth) {
				return $tac;
			}
		}

		return null;
	}


	/**
	 * Goes through everyone associated with this ticket (user owner, agent, participants)
	 * and fetches their preferred email address.
	 *
	 * Returns null if the person isn't on the ticket or if they don't have any email
	 * addresses.
	 *
	 * @param Person $person
	 * @return PersonEmail|null
	 */
	public function findEmailForPerson(Person $person)
	{
		if ($this->person == $person) {
			if ($this->person_email) {
				return $this->person_email;
			} else {
				return $this->person_email->primary_email;
			}
		} else if ($this->agent == $person) {
			return $this->agent->primary_email;
		} else {
			foreach ($this->participants as $part) {
				if ($part->person == $person) {
					if ($part->person_email) {
						return $part->person_email;
					} else {
						return $part->person->primary_email;
					}
				}
			}
		}

		return null;
	}


	/**
	 * Gets the access code which is an encoded ticket ID and authcode into one string.
	 *
	 * @return string
	 */
	public function getAccessCode()
	{
		$str = Util::baseEncode($this->id, 'letters');
		$str .= $this->auth;

		return $str;
	}


	/**
	 * Get the Message-ID field for an email regarding this ticket, witht he
	 * embedded PTAC code.
	 *
	 * @return string
	 */
	public function getUniqueEmailMessageId()
	{
		$uid = 'PTAC-' . $this->getAccessCode() . '.';
		$uid .= uniqid('', true) . '-' . App::getSetting('core.site_id');
		$uid .= '@' . md5(App::getSetting('core.site_url', 'deskpro'));

		return $uid;
	}


	/**
	 * Get the ID used in the interface for links etc.
	 *
	 * @return int
	 */
	public function getPublicId()
	{
		// !TODO controller by setting
		return $this->id;
	}


	/**
	 * Did this ticket originate from a gateway?
	 *
	 * @return bool
	 */
	public function isFromGateway()
	{
		if (strpos($this->creation_system, 'gateway') === 0) {
			return true;
		}

		return false;
	}


	/**
	 * Decodes an access code into a ticket id and the standalone auth.
	 *
	 * @param  $access_code
	 * @return array
	 */
	public static function decodeAccessCode($access_code)
	{
		$len = App::getSetting('core_tickets.ptac_auth_code_len');
		if (strlen($access_code) < ($len+1)) return false;

		$matches = Strings::extractRegexMatch('#^(.+)(.{'.$len.'})$#', $access_code, -1);
		if (!$matches) return false;

		list (, $ticket_id, $auth) = $matches;

		$ticket_id = Util::baseDecode($ticket_id, 'letters');

		return array(
			'ticket_id' => $ticket_id,
			'auth'      => $auth
		);
	}

	public function __clone()
	{
		parent::__clone();
		$this->_ticket_logger = null;
	}


	public function getTicketHash()
	{
		if (!$this->ticket_hash) {
			$this->initHashCode();
		}

		return $this->ticket_hash;
	}

	/**
	 * Resets the ticket hash
	 */
	public function recomputeHash()
	{
		$hashes = array();
		$hashes[] = sha1(
			$this->subject
			. $this->person->id
			. $this->getAgentId()
			. $this->getAgentTeamId()
			. $this->getDepartmentId()
			. $this->getCategoryId()
			. $this->getWorkflowId()
			. $this->getPriorityId()
			. $this->getProductId()
		);

		foreach ($this->custom_data as $d) {
			$hashes[] = sha1($d['field_id'] . $d['value'] . $d['input']);
		}

		if ($this->messages->containsKey(0)) {
			$hashes[] = $this->messages->get(0)->getMessageHash();
		}

		sort($hashes, \SORT_STRING);

		$this->ticket_hash = sha1(implode('', $hashes));
		$this->_onPropertyChanged('ticket_hash', '', $this->ticket_hash);
	}

	/**
	 * @ORM_Mapping\PrePersist
	 */
	public function initHashCode()
	{
		if ($this->ticket_hash) {
			return;
		}

		$this->recomputeHash();
	}


	/**
	 * @ORM_Mapping\PrePersist
	 */
	public function _preInsert()
	{
		// Get the new ref
		if (!$this->ref) {
			$this->ref = App::getRefGenerator()->generateReference('DeskPRO:Ticket');
		}

		if (!$this->_no_log && $this->_ticket_logger) {
			$this->getTicketLogger()->recordExtra('created', true);
		}
	}

	/**
	 * @ORM_Mapping\PreUpdate
	 * @ORM_Mapping\PrePersist
	 */
	public function _presaveTicketLogs()
	{
		if (!$this->_no_log && $this->_ticket_logger) {
			$this->_ticket_logger->preDone();
		}
	}

	public function resetTicketLogger()
	{
		$this->_initTicketLogger();
	}

	public function unsetTicketLogger()
	{
		unset($this->_ticket_logger);
	}

	/**
	 * @ORM_Mapping\PostUpdate
	 * @ORM_Mapping\PostPersist
	 */
	public function _saveTicketLogs()
	{
		if (!$this->_no_log && $this->_ticket_logger) {
			$this->_ticket_logger->done();
			$this->resetTicketLogger();
		}
	}

	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	public function getTicketLogger()
	{
		return $this->_ticket_logger;
	}


	/**
	 * @return \Application\DeskPRO\Labels\LabelManager
	 */
	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelTicket');
		}

		return $this->_label_manager;
	}

	public function copy()
	{
		$alt_ticket = new Ticket();

		$load = array(
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

		foreach ($load as $k) {
			$alt_ticket[$k] = $this[$k];
		}

		// Custom field data
		foreach ($this->custom_data as $custom_data) {
			$new_custom_data = clone $custom_data;
			$new_custom_data->ticket = $alt_ticket;

			$new_custom_data->addCustomData($new_custom_data);
		}

		return $alt_ticket;
	}


	public static function getStatusInt($status_code)
	{
		$status = $status_code;
		$hstatus = null;
		if (strpos($status, '.')) {
			list($status, $hstatus) = explode('.', $status, 2);
		}

		switch ($status) {
			case self::STATUS_AWAITING_AGENT:
				return 100;
			case self::STATUS_AWAITING_USER:
				return 110;
			case self::STATUS_RESOLVED:
				return 200;
			case self::STATUS_CLOSED:
				return 210;
			case self::STATUS_HIDDEN:
				switch ($hstatus) {
					case self::HIDDEN_STATUS_VALIDATING:
						return 300;
					case self::HIDDEN_STATUS_DELETED:
						return 310;
					case self::HIDDEN_STATUS_SPAM:
						return 320;
				}
				break;
		}

		return 0;
	}
}
