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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketChangeTracker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Class Ticket
 *
 * @property int $id
 * @property string $ref
 * @property string $auth
 * @property Language $language
 * @property Department $department
 * @property TicketCategory $category
 * @property TicketWorkflow $workflow
 * @property TicketPriority $priority
 * @property Product $product
 * @property Person $person
 * @property PersonEmail $person_email
 * @property PersonEmailValidating $person_email_validating
 * @property Person $agent
 * @property AgentTeam $agent_team
 * @property Organization $organization
 * @property ChatConversation $linked_chat
 * @property TicketAttachment[] $attachments
 * @property TicketAccessCode[] $access_codes
 * @property TicketMessage[] $messages
 * @property CustomDataTicket[] $custom_data
 * @property LabelTicket[] $labels
 * @property string $sent_to_address
 * @property EmailAccount $email_account
 * @property string $email_account_address
 * @property string $creation_system
 * @property string $creation_system_option
 * @property string $ticket_hash
 * @property string $status
 * @property string $hidden_status
 * @property string $validating
 * @property bool $is_hold
 * @property int $urgency
 * @property int $feedback_rating
 * @property \DateTime $date_feedback_rating
 * @property \DateTime $date_created
 * @property \DateTime $date_resolved
 * @property \DateTime $date_closed
 * @property \DateTime $date_first_agent_assign
 * @property \DateTime $date_first_agent_reply
 * @property \DateTime $date_last_agent_reply
 * @property \DateTime $date_last_user_reply
 * @property \DateTime $date_agent_waiting
 * @property \DateTime $date_user_waiting
 * @property \DateTime $date_status
 * @property int $total_user_waiting
 * @property int $total_to_first_reply
 * @property Person $locked_by_agent
 * @property \DateTime $date_locked
 * @property bool $has_attachments
 * @property string $subject
 * @property string $original_subject
 * @property array $properties
 * @property int $count_agent_replies
 * @property int $count_user_replies
 * @property string|null $worst_sla_status
 * @property array $waiting_times
 * @property TicketParticipant[] $participants
 * @property TicketCharge[] $charges
 * @property TicketSla[] $ticket_slas
 */
class Ticket extends DomainObject implements HighlightableModelInterface
{
	const TAC_AUTHCODE_LEN = 15;

	const CREATED_WEB_PERSON        = 'web.person';
	const CREATED_WEB_PERSON_PORTAL = 'web.person.portal';
	const CREATED_WEB_PERSON_WIDGET = 'web.person.widget';
	const CREATED_WEB_PERSON_EMBED  = 'web.person.embed';
	const CREATED_WEB_AGENT         = 'web.agent';
	const CREATED_WEB_AGENT_PORTAL  = 'web.agent.portal';
	const CREATED_WEB_API           = 'web.api';
	const CREATED_WEB_API_PERSON    = 'web.api.person';
	const CREATED_WEB_API_AGENT     = 'web.api.agent';
	const CREATED_GATEWAY_PERSON    = 'gateway.person';
	const CREATED_GATEWAY_AGENT     = 'gateway.agent';

	const STATUS_AWAITING_AGENT = 'awaiting_agent';
	const STATUS_AWAITING_USER  = 'awaiting_user';
	const STATUS_RESOLVED       = 'resolved';
	const STATUS_CLOSED         = 'closed';
	const STATUS_HIDDEN         = 'hidden';

	const HIDDEN_STATUS_VALIDATING  = 'validating';
	const HIDDEN_STATUS_SPAM        = 'spam';
	const HIDDEN_STATUS_DELETED     = 'deleted';
	const HIDDEN_STATUS_TEMP        = 'temp';

	/**#@+
	 * These strings in $notify_email_name have special meanings.
	 * NOTIFY_NAME_HELPDESK: The helpdesk name
	 * NOTIFY_NAME_PERSON: The person who sent the reply, or if no person (eg auto-response), then the helpdesk
	 */
	const NOTIFY_NAME_HELPDESK = '__DP_HELPDESK__';
	const NOTIFY_NAME_PERSON   = '__DP_PERSON__';
	/**#@-*/

	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * The original id (eg before a delete was made)
	 *
	 * @var int
	 */
	protected $_original_id;

	/**
	 * @var string
	 */
	protected $ref = null;

	/**
	 * @var int
	 */
	protected $auth;

	/**
	 * Parent ticket
	 *
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $parent_ticket = null;
	
	/**
	 * The language the ticket is in
	 *
	 * @var \Application\DeskPRO\Entity\Language
	 */
	protected $language = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 */
	protected $department = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 */
	protected $category = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketPriority
	 */
	protected $priority = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketWorkflow
	 */
	protected $workflow = null;

	/**
	 * @var \Application\DeskPRO\Entity\Product
	 */
	protected $product = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 */
	protected $person_email = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmailValidating
	 */
	protected $person_email_validating = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $agent = null;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 */
	protected $agent_team = null;

	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 */
	protected $organization = null;

	/**
	 * @var \Application\DeskPRO\Entity\ChatConversation
	 */
	protected $linked_chat = null;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $attachments;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $access_codes;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $messages;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $custom_data;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $labels;

	/**
	 * The email address the ticket was sent to if it came in via a gateway.
	 * This is a full string (e.g., including CC's) of the original.
	 *
	 * @var string
	 */
	protected $sent_to_address = '';

	/**
	 * The gateway this ticket originated from
	 *
	 * @var \Application\DeskPRO\Entity\EmailAccount
	 */
	protected $email_account = null;

	/**
	 * The email address (from list of to/cc) that matched with the email account.
	 *
	 * @var string
	 */
	protected $email_account_address = '';

	/**
	 * @var string
	 */
	protected $creation_system = 'unknown';

	/**
	 * Optional information about the creation system. For example, source URL the ticket came from.
	 *
	 * @var string
	 */
	protected $creation_system_option = '';

	/**
	 * @var string
	 */
	protected $ticket_hash;

	/**
	 * @var string
	 */
	protected $status = 'awaiting_agent';

	/**
	 * @var string
	 */
	protected $hidden_status = null;

	/**
	 * @var string
	 */
	protected $validating = null;

	/**
	 * Is the ticket on hold?
	 *
	 * @var bool
	 */
	protected $is_hold = false;

	/**
	 * @var int
	 */
	protected $urgency = 1;

	/**
	 * @var int
	 */
	protected $feedback_rating = null;

	/**
	 * @var \DateTime
	 */
	protected $date_feedback_rating = null;

	/**
	 * @var \DateTime
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 */
	protected $date_resolved = null;

	/**
	 * @var \DateTime
	 */
	protected $date_closed = null;

	/**
	 * @var \DateTime
	 */
	protected $date_first_agent_assign = null;

	/**
	 * @var \DateTime
	 */
	protected $date_first_agent_reply = null;

	/**
	 * @var \DateTime
	 */
	protected $date_last_agent_reply = null;

	/**
	 * @var \DateTime
	 */
	protected $date_last_user_reply = null;

	/**
	 * @var \DateTime
	 */
	protected $date_agent_waiting = null;

	/**
	 * @var \DateTime
	 */
	protected $date_user_waiting = null;

	/**
	 * @var \DateTime
	 */
	protected $date_status = null;

	/**
	 * @var int
	 */
	protected $total_user_waiting = 0;

	/**
	 * @var int
	 */
	protected $total_to_first_reply = 0;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $locked_by_agent = null;

	/**
	 * @var \DateTime
	 */
	protected $date_locked = null;

	/**
	 * @var bool
	 */
	protected $has_attachments = false;

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var string
	 */
	protected $original_subject = '';

	/**
	 * @var array
	 */
	protected $properties = null;

	/**
	 * @var int
	 */
	protected $count_agent_replies = 0;

	/**
	 * @var int
	 */
	protected $count_user_replies = 0;

	/**
	 * @var string|null
	 */
	protected $worst_sla_status = null;

	/**
	 * @var array
	 */
	protected $waiting_times = array();

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $participants;

	/**
	 * Array cache of user participants
	 * @var array
	 * @see getUserParticipants
	 */
	protected $_user_participants;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $charges;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $ticket_slas;

	/**
	 * An exploded version of sent_to_addresses
	 *
	 * @var array
	 */
	protected $_sent_to_addresses;

	protected $_label_manager = null;

	/**
	 * @var \Orb\Util\WorkHoursSet|null
	 */
	protected $_work_hours_set = null;

    /**
     * The search result highlights
     *
     * @var array
     */
    protected $_search_highlights;

	/**
	 * If the tikcet was created from an email just now, then this is the reader
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	public $email_reader;

	/**
	 * The action the email reader was used for (reply/note/action)
	 * @var string
	 */
	public $email_reader_action;

    /**
	 * @internal
	 */
	public $__dp_is_processing_ticket = false;

	/**
	 * @internal
	 */
	public $__dp_last_process_save = null;

	/**
	 * @internal
	 */
	public $__dp_ticket_change_tracker = null;

	/**
	 * @internal
	 */
	public $__dp_auto_ticket_process = false;

	/**
	 * @var bool
	 */
	public $__dp_is_autogen_ref = false;

	/**
	 * @var bool
	 */
	public $_is_new = false;

	public function __construct()
	{
		$this->_original_id  = null;
		$this->_is_new       = true;
		$this->participants  = new ArrayCollection();
		$this->messages      = new ArrayCollection();
		$this->custom_data   = new ArrayCollection();
		$this->labels        = new ArrayCollection();
		$this->access_codes  = new ArrayCollection();
		$this->attachments   = new ArrayCollection();
		$this->charges       = new ArrayCollection();
		$this->ticket_slas   = new ArrayCollection();

		// Default ref (is reset with ref generator)
		$this->ref = Strings::random(10, Strings::CHARS_ALPHA_IU) . '-' . date('YzB');

		// flag used in manager to signal that we should overwrite this with a real ref generator ref
		$this->__dp_is_autogen_ref = true;

		$this['date_created'] = new \DateTime();
		$this['date_status'] = new \DateTime();

		$this['auth'] = Strings::random(self::TAC_AUTHCODE_LEN, Strings::CHARS_KEY);

		$this->__dp_auto_ticket_process = true;
	}

	/**
	 * By default, all ticket chagnes go through the full ticket processing routines
	 * (triggers, filters etc) automatically on every flush.
	 *
	 * This is mostly for legacy reasons though. It's recommneded you always handle it yourself.
	 * So if you are manually managing the ticket will save the ticket through the TicketManager,
	 * you should disable auto-processing.
	 */
	public function disableAutoTicketProcess()
	{
		$this->__dp_auto_ticket_process = false;
	}

	/**
	 * Enable auto ticket processing
	 * @see disableAutoTicketProcess
	 */
	public function enableAutoTicketProcess()
	{
		$this->__dp_auto_ticket_process = true;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return int|null
	 */
	public function getOriginalId()
	{
		return $this->_original_id;
	}


	/**
	 * @return string
	 */
	public function getSubject()
	{
		if (!$this->subject) {
			return '(no subject)';
		}

		return $this->subject;
	}


	/**
	 * Get an array of addresses the ticket was sent To or CC's
	 *
	 * @return array
	 */
	public function getSentToAddresses()
	{
		if (!$this->sent_to_address) {
			return array();
		}

		if ($this->_sent_to_addresses !== null) {
			return $this->_sent_to_addresses;
		}

		$this->_sent_to_addresses = explode(',', $this->sent_to_address);
		$this->_sent_to_addresses = array_combine($this->_sent_to_addresses, $this->_sent_to_addresses);
		return $this->_sent_to_addresses;
	}


	/**
	 * Check if an address was in To or CC
	 *
	 * @param $address
	 * @return bool
	 */
	public function hasSentToAddress($address)
	{
		$address = strtolower($address);
		$this->getSentToAddresses();

		return isset($this->_sent_to_addresses[$address]);
	}


	/**
	 * @param string $addresses
	 */
	public function setSentToAddress($addresses)
	{
		if (is_array($addresses)) {
			$addresses = implode(',', $addresses);
		}

		$addresses = strtolower($addresses);

		$this->setModelField('sent_to_address', $addresses);
		$this->_sent_to_addresses = null;
	}


	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		if (!$subject) {
			$subject = '';
		}

		$subject = Strings::standardEol($subject);
		$subject = Strings::trimLines($subject);
		$subject = preg_replace("#\n+#", ' ', $subject);

		if (!$subject) {
			$subject = "(No Subject)";
		}

		$this->setModelField('subject', $subject);

		if (!$this->original_subject) {
			$this->setProcessedOriginalSubject($subject);
		}
	}


	/**
	 * Parse off common prefixes on subjects and then set the original subject
	 *
	 * @param $subject
	 */
	public function setProcessedOriginalSubject($subject)
	{
		do {
			$orig = $subject;
			$subject = preg_replace('#^(RE|VS|AW|SV|FW|FWD|VL|WG|FS|VB|RV|VS):\s*#i', '', $subject);
		} while ($orig != $subject);

		$this->setModelField('original_subject', $subject);
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person[]
	 */
	public function getUserParticipants()
	{
		$ret = array();

		foreach ($this['participants'] as $p) {
			if (!$p['person']['is_agent']) {
				$ret[] = $p->person;
			}
		}

		return $ret;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person[]
	 */
	public function getAgentParticipants()
	{
		$ret = array();
		foreach ($this->participants as $p) {
			if ($p->person['is_agent']) {
				$ret[] = $p->person;
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
	 * @param string $email_address
	 * @return Person
	 */
	public function findUserByEmail($email_address)
	{
		$email_address = strtolower($email_address);

		// The author
		if ($this->person->findEmailAddress($email_address)) {
			return $this->person;

		// Any of the participants
		} else {
			foreach ($this->getUserParticipants() as $person) {
				if ($person->findEmailAddress($email_address)) {
					return $person;
				}
			}
		}

		return null;
	}


	/**
	 * Try to find an agent that is part of this ticket based on an email addres
	 *
	 * @param string $email_address
	 * @return Person|null
	 */
	public function findAgentByEmail($email_address)
	{
		if ($this->person->is_agent && $this->person->findEmailAddress($email_address)) {
			return $this->person;
		} else {
			foreach ($this->participants as $part) {
				if ($part->person->is_agent && $part->person->findEmailAddress($email_address)) {
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
	 */
	public function modifyUrgency($mod)
	{
		$old_u = $this->urgency;
		$new_u = \Orb\Util\Numbers::bound($old_u + $mod, 1, 10);

		if ($old_u != $new_u) {
			$this->urgency = $new_u;

			$this->_onPropertyChanged('urgency', $old_u, $new_u);
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
		$new_u = \Orb\Util\Numbers::bound($set, 1, 10);

		if ($old_u != $new_u) {
			$this->urgency = $new_u;
			$this->_onPropertyChanged('urgency', $old_u, $new_u);
		}
	}


	/**
	 * @param string $rating
	 */
	public function setFeedbackRating($rating)
	{
		$this->setModelField('feedback_rating', $rating);
		$this->setModelField('date_feedback_rating', new \DateTime());
	}


	/**
	 * @return string
	 */
	public function getFeedbackRatingType()
	{
		if ($this->feedback_rating == 1) return 'positive';
		elseif ($this->feedback_rating == -1) return 'negative';
		else return 'neutral';
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


	/**
	 * Check if there exists a person on this ticket with a particular email address.
	 *
	 * @param string $email_address
	 * @return Person|bool
	 */
	public function hasParticipantEmailAddress($email_address)
	{
		if ($this->agent && $this->agent->hasEmailAddress($email_address)) {
			return $this->agent;
		}

		if ($this->person && $this->person->hasEmailAddress($email_address)) {
			return $this->person;
		}

		foreach ($this->participants as $p) {
			if ($p->person->hasEmailAddress($email_address)) {
				return $p->person;
			}
		}

		return false;
	}


	/**
	 * Check if a person ID or a person object is current a participant.
	 *
	 * @param  $person_or_id
	 * @param $only_parts Only check participants (not assigned agent)
	 * @return bool
	 */
	public function hasParticipantPerson($person_or_id)
	{
		$person_id = $person_or_id;
		if ($person_or_id instanceof Person) {
			$person_id = $person_or_id['id'];
		}

		// User not commited yet, so obviously they dont exist
		if (!$person_id) {
			return false;
		}

		foreach ($this->participants as $p) {
			if ($p->person->id == $person_id) {
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

		if ($this->person && $person->id == $this->person->id && DP_INTERFACE != 'agent') {
			return null;
		}

		if ($ticket_part = $this->hasParticipantPerson($person)) {
			return $ticket_part;
		}

		$ticket_part = new TicketParticipant();
		$ticket_part['person'] = $person;
		$ticket_part['ticket'] = $this;
		$this->participants->add($ticket_part);

		if ($this->_user_participants !== null AND !$person['is_agent']) {
			$this->_user_participants[] = $ticket_part;
		}

		$this->_onPropertyChanged('participants', null, $this->participants);

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
		if ($person && !($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		if (!$person) {
			return null;
		}

		foreach ($this->participants as $k => $p) {
			if ($p['person']->getId() == $person->getId()) {
				$this->participants->remove($k);
				$this->_onPropertyChanged('participants', null, $this->participants);
				return $p;
			}
		}

		return null;
	}


	/**
	 * @param TicketParticipant $part
	 */
	public function addParticipant(TicketParticipant $part)
	{
		$part->ticket = $this;
		$this->participants->add($part);
		$this->_onPropertyChanged('participants', null, $this->participants);
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
			$this->_onPropertyChanged('participants', null, $this->participants);
		}

		$new_agent_ids = array_diff($set_agent_ids, $got_agent_ids);

		if ($new_agent_ids) {
			foreach ($new_agent_ids as $agent_id) {
				$part = new \Application\DeskPRO\Entity\TicketParticipant();
				$part['person_id'] = $agent_id;

				$this->addParticipant($part);
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
	 * @param Person $agent
	 * @param int $time
	 * @param int $amount
	 * @param string $comment
	 * @return TicketCharge|null
	 */
	public function addCharge(Person $agent, $time, $amount = null, $comment = '')
	{
		if ($time !== null) {
			$time = intval($time);
			if ($time == 0) {
				$time = null;
			}
		}
		if ($amount !== null) {
			$amount = floatval($amount);
			if ($amount == 0) {
				$amount = null;
			}
		}

		if ($time === null && $amount === null) {
			return null;
		}

		$charge = new TicketCharge();
		$charge->charge_time = $time;
		$charge->amount = $amount;
		$charge->comment = strval($comment);
		$charge->ticket = $this;
		$charge->person = $this->person;
		$charge->organization = $this->organization;
		$charge->agent = $agent;

		$this->charges->add($charge);

		$this->_onPropertyChanged('charges', null, $this->charges);

		return $charge;
	}


	/**
	 * @param Sla $sla
	 * @return TicketSla
	 */
	public function addSla(Sla $sla)
	{
		foreach ($this->ticket_slas AS $ticket_sla) {
			if ($ticket_sla->sla->id == $sla->id) {
				return $ticket_sla;
			}
		}

		$ticket_sla = new TicketSla();
		$ticket_sla->ticket = $this;
		$ticket_sla->sla = $sla;

		$this->ticket_slas->add($ticket_sla);
		$this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);

		return $ticket_sla;
	}


	/**
	 * @param Sla $sla
	 * @return TicketSla|null
	 */
	public function removeSla(Sla $sla)
	{
		foreach ($this->ticket_slas AS $k => $ticket_sla) {
			if ($ticket_sla->sla->id == $sla->id) {
				$this->ticket_slas->remove($k);
				$this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);
				$this->updateWorstSlaStatus();
				return $ticket_sla;
			}
		}

		return null;
	}

	/**
	 * Remove all SLAs from ticket
	 */
	public function removeAllSlas()
	{
		$this->ticket_slas->clear();
		$this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);

		$this->setModelField('worst_sla_status', null);
	}

	/**
	 * @param Sla $sla
	 * @return bool
	 */
	public function hasSla(Sla $sla)
	{
		foreach ($this->ticket_slas AS $ticket_sla) {
			if ($ticket_sla->sla->id == $sla->id) {
				return $ticket_sla;
			}
		}

		return false;
	}


	/**
	 * @param $sla_id
	 * @return null
	 */
	public function getSlaById($sla_id)
	{
		foreach ($this->ticket_slas AS $ticket_sla) {
			if ($ticket_sla->sla->id == $sla_id) {
				return $ticket_sla;
			}
		}

		return null;
	}


	/**
	 * @return array
	 */
	public function getSlaIds()
	{
		$ids = array();
		foreach ($this->ticket_slas AS $ticket_sla) {
			$ids[] = $ticket_sla->sla->id;
		}

		return $ids;
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
		if ($message->person['is_agent'] && !(defined('DP_INTERFACE') && DP_INTERFACE == 'user')) {
			if (!$message->is_agent_note && !$this->_is_new) {
				if (!$this->date_last_agent_reply || $this->date_last_agent_reply < $now) {
					$this['date_last_agent_reply'] = $now;
				}

				if (!$this->date_first_agent_reply && !$message->is_agent_note) {
					$this['date_first_agent_reply'] = $now;
					$this['total_to_first_reply'] = $this->date_first_agent_reply->getTimestamp() - $this->date_created->getTimestamp();
				}
			}
		} else {
			if (!$this->date_last_user_reply || $this->date_last_user_reply < $now) {
				$this['date_last_user_reply'] = $now;
			}
		}

		$this->_onPropertyChanged('messages', null, $this->messages, true);
		$this->getStateChangeRecorder()->record('message', null, $message);
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

		$this->_onPropertyChanged('attachments', null, $this->attachments);
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

		$this->_onPropertyChanged('custom_data', null, $this->participants);

		return $custom_data;
	}


	/**
	 * @param $field
	 */
	public function removeCustomDataForField($field)
	{
		$parent_id = null;
		$field_id = $field['id'];
		if ($field->parent) {
			$parent_id = $field->parent['id'];
		}

		$change = false;
		foreach ($this->custom_data as $data) {
			if ($data['field_id'] == $field_id OR $data['field_id'] == $parent_id) {
				$change = true;
				$this->custom_data->removeElement($data);
			}
		}

		if ($change) {
			$this->_onPropertyChanged('custom_data', null, $this->participants);
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

		$this->_onPropertyChanged('custom_data', null, $this->participants);
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
		if ($ret = $this->findLabelByString($label->label)) {
			return $ret;
		}

		$label->ticket = $this;
		$this->labels->add($label);
		$this->_onPropertyChanged('labels', null, $this->labels);
		return $label;
	}


	/**
	 * @param string $l
	 * @return LabelTicket
	 */
	public function addLabelByString($l)
	{
		if ($ret = $this->findLabelByString($l)) {
			return $ret;
		}

		$label = new LabelTicket();
		$label->label = $l;
		$label->ticket = $this;
		$this->labels->add($label);
		$this->_onPropertyChanged('labels', null, $this->labels);

		return $label;
	}


	/**
	 * @param string $l
	 * @return LabelTicket|null
	 */
	public function removeLabelByString($l)
	{
		$x = new LabelTicket();
		$x->label = $l;

		foreach ($this->labels as $idx => $label) {
			if ($label->label == $x->label) {
				$this->labels->remove($idx);
				$this->_onPropertyChanged('labels', null, $this->labels);
				return $label;
			}
		}

		return null;
	}


	/**
	 * @param string $l
	 * @return LabelTicket|null
	 */
	public function findLabelByString($l)
	{
		$x = new LabelTicket();
		$x->label = $l;

		if (($idx = $this->labels->indexOf($x->label)) !== false) {
			return $this->labels->get($idx);
		}

		return null;
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
			$this['language'] = $person->getRealLanguage();
		}

		if ($person->organization) {
			$this['organization'] = $person->organization;
		}

		if ($this->person_email && $this->person_email->person->getId() != $person->getId()) {
			$this['person_email'] = null;
		}
	}


	/**
	 * @deprecated
	 * @return PersonEmail
	 */
	public function getPersonEmail()
	{
		if ($this->person_email) {
			return $this->person_email;
		} else {
			return $this->person['primary_email'];
		}
	}


	/**
	 * Gets the email address that sholud be used for this ticket.
	 *
	 * @return PersonEmail
	 */
	public function getTicketPersonEmail()
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

	public function getEmailAccountId()
	{
		if (!$this->email_account) {
			return 0;
		}

		return $this->email_account->getId();
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

	public function isLangSet()
	{
		return $this->language ? true : false;
	}

	public function getRealLanguage()
	{
		return $this->language;
	}

	public function getLanguage()
	{
		if ($this->language) {
			return $this->language;
		} elseif ($this->person && $this->person->getRealLanguage()) {
			return $this->person->getRealLanguage();
		}

		return null;
	}

	public function getLanguageId()
	{
		$l = $this->getLanguage();
		return $l ? $l->getId() : 0;
	}

	public function setLanguageId($id)
	{
		if ($id) {
			$lang = App::getOrm()->getRepository('DeskPRO:Language')->find($id);
			$this['language'] = $lang;
		} else {
			$this['language'] = null;
		}
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
				throw new \InvalidArgumentException("$id is not an agent");
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
		$this->setModelField('locked_by_agent', $agent);
		if ($agent) {
			$this->setModelField('date_locked', new \DateTime());
		} else {
			$this->setModelField('date_locked', null);
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

	public function hasLock()
	{
		return $this->locked_by_agent ? true : false;
	}

	public function isLocked(Person $current_agent = null)
	{
		if (!$this->locked_by_agent) {
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
		return $this->isArchived();
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
	 * @return void
	 */
	public function setFlagForPerson($person, $color = null)
	{
		if ($color == 'none') $color = null;

		if ($color) {
			App::getDb()->replace('tickets_flagged', array(
				'person_id' => $person['id'],
				'ticket_id' => $this->id,
				'color'     => $color
			));
		} else {
			App::getDb()->delete('tickets_flagged', array(
				'person_id' => $person['id'],
				'ticket_id' => $this->id,
			));
		}
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
	 * Is the ticket archived? Archived tickets are closed to replies.
	 *
	 * @return bool
	 */
	public function isArchived()
	{
		if ($this->status != 'closed') {
			return false;
		}

		return true;
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

		if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
			$secs += time() - $this->date_user_waiting->getTimestamp();
		}

		return $secs;
	}

	public function getTotalUserWaitingWorkTime()
	{
		$work_hours_set = $this->getWorkHoursSet();

		$time = 0;
		if ($this->waiting_times) {
			foreach ($this->waiting_times AS $waiting) {
				if ($waiting['type'] == 'user') {
					$time += $work_hours_set->getWorkTimeBetween($waiting['start'], $waiting['end']);
				}
			}
		}

		if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
			$time += $work_hours_set->getWorkTimeBetween($this->date_user_waiting);
		}

		return $time;
	}

	public function getCurrentUserWaitingTime()
	{
		if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
			return time() - $this->date_user_waiting->getTimestamp();
		}

		return null;
	}

	public function getCurrentUserWaitingWorkTime()
	{
		if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
			return $this->getWorkHoursSet()->getWorkTimeBetween($this->date_user_waiting);
		}

		return null;
	}

	public function getWorkTimeToFirstReply()
	{
		if ($this->date_first_agent_reply) {
			return $this->getWorkHoursSet()->getWorkTimeBetween($this->date_created, $this->date_first_agent_reply);
		}

		return null;
	}


	/**
	 * Get how long, in seconds, the ticket was open for. This only applies
	 * for tikcets that are resolved (or closed).
	 *
	 * @return int
	 */
	public function getTimeUntilResolution()
	{
		if (!$this->date_resolved && !$this->date_closed) {
			return null;
		}

		$date = $this->date_resolved;
		if (!$date || ($this->date_closed && $date > $this->date_closed)) {
			$date = $this->date_closed;
		}

		$secs = $date->getTimestamp() - $this->date_created->getTimestamp();

		return $secs;
	}

	public function getWorkTimeUntilResolution()
	{
		if (!$this->date_resolved && !$this->date_closed) {
			return null;
		}

		$date = $this->date_resolved;
		if (!$date || ($this->date_closed && $date > $this->date_closed)) {
			$date = $this->date_closed;
		}

		return $this->getWorkHoursSet()->getWorkTimeBetween($this->date_created, $date);
	}


	/**
	 * @return \DateTime
	 */
	public function getLastActivityDate()
	{
		$dates = array();
		if ($this->date_last_agent_reply) {
			$dates[] = $this->date_last_agent_reply;
		}
		if ($this->date_last_user_reply) {
			$dates[] = $this->date_last_user_reply;
		}

		if (!$dates) {
			return $this->date_created;
		}

		$use_date = $this->date_created;
		foreach ($dates as $d) {
			if ($d > $use_date) {
				$use_date = $d;
			}
		}

		return $use_date;
	}


	public function setStatus($status)
	{
		$this['date_status'] = new \DateTime();

		$old_status  = $this->status;

		if ($status != 'awaiting_agent' && $old_status == 'awaiting_agent' && $this->date_user_waiting) {
			$this->setModelField('total_user_waiting', $this->total_user_waiting + time() - $this->date_user_waiting->getTimestamp());
			$this->addWaitingTimeRecord('user', $this->date_user_waiting);
		}
		if ($status == 'awaiting_agent' && !$this->date_user_waiting) {
			$this->setModelField('date_user_waiting', new \DateTime());
		}
		if ($status != 'awaiting_agent' && $this->date_user_waiting) {
			$this->setModelField('date_user_waiting', null);
		}

		if ($status != 'awaiting_user' && $old_status == 'awaiting_user' && $this->date_agent_waiting) {
			$this->addWaitingTimeRecord('agent', $this->date_agent_waiting);
		}
		if ($status == 'awaiting_user' && !$this->date_agent_waiting) {
			$this->setModelField('date_agent_waiting', new \DateTime());
		}
		if ($status != 'awaiting_user' && $this->date_agent_waiting) {
			$this->setModelField('date_agent_waiting', null);
		}

		if ($status == 'closed' && !$this->date_closed) {
			$this['date_closed'] = new \DateTime();
		}
		if ($status != 'closed' && $this->date_closed) {
			$this->setModelField('date_closed', null);
		}

		if ($status == 'resolved' && !$this->date_resolved) {
			$this['date_resolved'] = new \DateTime();
		}
		if ($status != 'resolved' && $this->date_resolved) {
			$this->setModelField('date_resolved', null);
		}

		if ($status != 'awaiting_agent' && $this->is_hold) {
			$this['is_hold'] = false;
		}

		$old_hstatus = $this->hidden_status;
		$old_status_code = "$old_status.$old_hstatus";

		$status_code = $status;
		$hstatus = null;
		if (strpos($status, '.')) {
			list($status, $hstatus) = explode('.', $status, 2);
		}

		if (!$status || !in_array($status, array(
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
			self::HIDDEN_STATUS_VALIDATING,
			self::HIDDEN_STATUS_TEMP
		))) {
			throw new \InvalidArgumentException("Invalid hidden status `$hstatus`");
		}

		if ($hstatus && $status != 'hidden') {
			throw new \InvalidArgumentException("Invalid status must be hidden to set a hidden status, got `$status` instead.");
		}

		$this->setModelField('status', $status);
		$this->setModelField('hidden_status', $hstatus);

		if ($old_status_code == 'hidden.deleted' && $status_code != 'hidden.deleted') {
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
		if ($this->status == 'hidden') {
			return 'hidden.' . ($this->hidden_status ?: 'validating');
		} else {
			return $this->status;
		}
	}

	public function setIsHold($is_hold)
	{
		if ($is_hold) {
			$this->setStatus(self::STATUS_AWAITING_AGENT);
			$this->setModelField('is_hold', true);
		} else {
			$this->setModelField('is_hold', false);
		}
	}


	/**
	 * Undelete a ticket.
	 *
	 * This will set the status to 'awaiting_agent' if it wasn't changed before.
	 */
	public function undeleteTicket()
	{
		if (!$this->id) {
			return;
		}

		$del = $this->getDeletionRecord();
		if (!$del) {
			return;
		}

		if ($this->status == 'hidden') {
			$this->setModelField('status', self::STATUS_AWAITING_AGENT);
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
		$del['old_ptac']      = $this->auth;
		$del['by_person']     = $person;
		$del['new_ticket_id'] = 0;
		$del['reason']        = $reason;

		$this->setStatus('hidden.deleted');

		App::getOrm()->persist($del);
		App::getOrm()->flush($del);
		App::getOrm()->persist($this);
	}

	public function updateWorstSlaStatus()
	{
		$status = $this->getWorstSlaStatus();
		$this->setModelField('worst_sla_status', $status);
		return $status;
	}

	public function getWorstSlaStatus()
	{
		if (!count($this->ticket_slas)) {
			return null;
		}

		$status = null;
		foreach ($this->ticket_slas AS $ticket_sla) {
			if ($ticket_sla->is_completed) {
				continue;
			}

			if (!$status) {
				$status = $ticket_sla->sla_status;
			} else if ($ticket_sla->sla_status == 'fail') {
				$status = 'fail';
			} else if ($ticket_sla->sla_status == 'warning' && $status !== 'fail') {
				$status = 'warning';
			}
		}

		return $status;
	}

	public function addWaitingTimeRecord($type, $start_ts, $end_ts = null)
	{
		$start_ts = ($start_ts instanceof \DateTime ? $start_ts->getTimestamp() : intval($start_ts));
		$end_ts = ($end_ts instanceof \DateTime ? $end_ts->getTimestamp() : intval($end_ts));

		if (!$end_ts) {
			$end_ts = time();
		}

		if ($end_ts <= $start_ts) {
			return;
		}

		if (!is_array($this->waiting_times)) {
			$this->waiting_times = array();
		}

		$old = $this->waiting_times;
		$this->waiting_times[] = array(
			'type' => $type,
			'start' => $start_ts,
			'end' => $end_ts,
			'length' => ($end_ts - $start_ts)
		);
		$this->_onPropertyChanged('waiting_times', $old, $this->waiting_times);
	}

	public function getWaitingTimes()
	{
		if (!is_array($this->waiting_times)) {
			return array();
		} else {
			return $this->waiting_times;
		}
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
				return $this->person->primary_email;
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
	 * @return string
	 */
	public function getEmailReferencesHeader()
	{
		$uid = 'TICKET-' . $this->getAccessCode() . '.';
		$uid .= App::getSetting('core.site_id');
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
		if (App::getSetting('core.tickets.use_ref')) {
			return $this->ref;
		}

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
		$len = Ticket::TAC_AUTHCODE_LEN;
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
			. ($this->person ? $this->person->id : '')
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
	 */
	public function initHashCode()
	{
		if ($this->ticket_hash) {
			return;
		}

		$this->recomputeHash();
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


	/**
	 * Copy this ticket properties to a new ticket.
	 *
	 * @return Ticket
	 */
	public function copy()
	{
		$ticket = new Ticket();
		$this->copyTo($ticket);
		return $ticket;
	}


	/**
	 * Copy this ticket properties on to another ticket.
	 *
	 * @param Ticket $ticket
	 */
	public function copyTo(Ticket $ticket)
	{
		$load = array(
			'agent',
			'agent_team',
			'person',
			'person_email',
			'department',
			'category',
			'product',
			'workflow',
			'language',
			'organization',
			'status',
			'hidden_status',
			'subject',
			'is_hold',
			'urgency',
		);

		foreach ($load as $k) {
			$ticket[$k] = $this[$k];
		}

		// Custom field data
		foreach ($this->custom_data as $custom_data) {
			$new_custom_data = clone $custom_data;
			$new_custom_data->ticket = $ticket;

			$ticket->addCustomData($new_custom_data);
		}
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

	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		if ($deep) {
			$data['labels'] = array();
			foreach ($this->labels AS $label) {
				$data['labels'][] = $label['label'];
			}
		}

		$data['total_user_waiting_real'] = $this->getRealTotalUserWaiting();
		$data['total_user_waiting_work'] = $this->getTotalUserWaitingWorkTime();
		$data['current_user_waiting'] = $this->getCurrentUserWaitingTime();
		$data['current_user_waiting_work'] = $this->getCurrentUserWaitingWorkTime();
		$data['total_to_first_reply_work'] = $this->getWorkTimeToFirstReply();
		$data['total_to_resolution'] = $this->getTimeUntilResolution();
		$data['total_to_resolution_work'] = $this->getWorkTimeUntilResolution();

		if ($this->agent) {
			$data['agent']['display_name'] = $this->agent->getDisplayNameUser();
			$data['agent']['display_name_real'] = $this->agent->getDisplayName();
		}

		$data['access_code'] = $this->getAccessCode();
		$data['access_code_email_body_token'] = '(#' . $this->getAccessCode() . ')';
		$data['access_code_email_header_token'] = 'PTAC-' . $this->getAccessCode();

		// Render custom fields to text values
		$field_manager = App::getContainer()->getSystemService('ticket_fields_manager');
		$field_manager->addApiData($this, $data);

		return $data;
	}


	/**
	 * @param string $string
	 * @param Person|null $performer
	 * @param bool $escape
	 * @param bool $to_user
	 * @return mixed
	 */
	public function replaceVarsInString($string, Person $performer = null, $escape = false, $to_user = true)
	{
		$repl = array();

		$display_name = $to_user ? $this->person->getDisplayNameUser() : $this->person->getDisplayName();

		$repl = array_merge(array(
			'user.name'                   => $display_name,
			'user.email'                  => $this->person->getPrimaryEmailAddress(),
			'user.organization_position'  => $this->person->organization_position,

			'org.name' => $this->person->organization ? $this->person->organization->name : '',
		), $repl);

		// Custom user fields: {{ user.field23 }}
		$field_manager = App::getSystemService('person_fields_manager');
		$custom_fields = $field_manager->getRenderedToTextForObject($this->person);
		foreach ($custom_fields as $f) {
			$repl["user.field{$f['id']}"] = $f['rendered'];
		}

		// Custom org fields: {{ agent.field23 }}
		if ($this->person->organization) {
			$field_manager = App::getSystemService('org_fields_manager');
			$custom_fields = $field_manager->getRenderedToTextForObject($this->person->organization);
			foreach ($custom_fields as $f) {
				$repl["org.field{$f['id']}"] = $f['rendered'];
			}
		}

		if (!$performer && App::getCurrentPerson() && App::getCurrentPerson()->getId()) {
			$performer = App::getCurrentPerson();
		}

		if ($performer) {
			$display_name = $to_user ? $performer->getDisplayNameUser() : $performer->getDisplayName();

			$repl = array_merge(array(
				'performer.name'                   => $display_name,
				'performer.email'                  => $performer->getPrimaryEmailAddress(),
				'performer.organization_position'  => $performer->organization_position,

				'performer.org.name' => $performer->organization ? $performer->organization->name : '',
			), $repl);

			$field_manager = App::getSystemService('person_fields_manager');
			$custom_fields = $field_manager->getRenderedToTextForObject($performer);
			foreach ($custom_fields as $f) {
				$repl["performer.field{$f['id']}"] = $f['rendered'];
			}

			if ($performer->organization) {
				$field_manager = App::getSystemService('org_fields_manager');
				$custom_fields = $field_manager->getRenderedToTextForObject($performer->organization);
				foreach ($custom_fields as $f) {
					$repl["performer.org.field{$f['id']}"] = $f['rendered'];
				}
			}
		} else {
			$repl = array_merge(array(
				'performer.name'                   => '',
				'performer.email'                  => '',
				'performer.organization_position'  => '',
				'performer.org.name' => '',
			), $repl);
		}

		if ($this->agent) {
			$agent_display_name = $to_user ? $this->agent->getDisplayNameUser() : $this->agent->getDisplayName();
		} else {
			$agent_display_name = '';
		}

		$repl = array_merge(array(
			'ticket.id'               => $this->id,
			'ticket.ref'              => $this->ref,
			'ticket.subject'          => $this->subject,
			'ticket.department'       => $this->department ? $this->department->full_title : '',
			'ticket.product'          => $this->product ? $this->product->full_title : '',
			'ticket.category'         => $this->category ? $this->category->full_title : '',
			'ticket.workflow'         => $this->workflow ? $this->workflow->title : '',
			'ticket.priority'         => $this->priority ? $this->priority->title : '',

			'agent.name'     => $agent_display_name,
			'agent.email'    => $this->agent ? $this->agent->getPrimaryEmailAddress() : '',

			'agent_team.name' => $this->agent_team ? $this->agent_team->name : '',
		), $repl);

		// Custom ticket fields: {{ ticket.field23 }}
		$field_manager = App::getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getRenderedToTextForObject($this);
		foreach ($custom_fields as $f) {
			$repl["ticket.field{$f['id']}"] = $f['rendered'];
		}

		foreach ($repl as $k => $v) {
			if ($escape) {
				$v = htmlspecialchars($v);
			}
			$string = str_replace("{{ $k }}", $v, $string);
			$string = str_replace("{{{$k}}}", $v, $string);
		}

		return $string;
	}

	public function getPath()
	{
		return App::getRouter()->generate('user_tickets_view', array('ticket_ref' => $this->getAccessCode()));
	}

	public function getLink()
	{
		return App::getRouter()->generateUrl('user_tickets_view', array('ticket_ref' => $this->getAccessCode()));
	}

	public function isAgentCreated()
	{
		return strpos($this->creation_system, '.agent') !== false;
	}

	public function getWorkHoursSet()
	{
		if (!$this->_work_hours_set) {
			$this->_work_hours_set = new \Orb\Util\WorkHoursSetAll();
		}

		return $this->_work_hours_set;
	}


	/**
	 * Get property from properties array
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function getProperty($key, $default = null)
	{
		return ($this->properties !== null && isset($this->properties[$key]) ? $this->properties[$key] : $default);
	}


	/**
	 * Set properties
	 *
	 * @param  $key
	 * @param  $value
	 * @return void
	 */
	public function setProperty($key, $value)
	{
		$old = $this->properties;

		if ($value === null) {
			if ($this->properties) {
				unset($this->properties[$key]);
			}
			if (!$this->properties) {
				$this->properties = null;
			}
		} else {
			if ($this->properties === null) {
				$this->properties = array();
			}
			$this->properties[$key] = $value;
		}

		$this->_onPropertyChanged('properties', $old, $this->properties);
	}


	/**
	 * Returns the data as it would be returned from the database
	 *
	 * @return array
	 */
	public function getDbRow()
	{
		$row_data = array(
			'id'                           => $this->id,
			'language_id'                  => $this->language ? $this->language->id : null,
			'department_id'                => $this->department ? $this->department->id : null,
			'category_id'                  => $this->category ? $this->category->id : null,
			'priority_id'                  => $this->priority ? $this->priority->id : null,
			'workflow_id'                  => $this->workflow ? $this->workflow->id : null,
			'product_id'                   => $this->product ? $this->product->id : null,
			'person_id'                    => $this->person ? $this->person->id : null,
			'person_email_id'              => $this->person_email ? $this->person_email->id : null,
			'person_email_validating_id'   => $this->person_email_validating ? $this->person_email_validating->id : null,
			'agent_id'                     => $this->agent ? $this->agent->id : null,
			'agent_team_id'                => $this->agent_team ? $this->agent_team->id : null,
			'organization_id'              => $this->organization ? $this->organization->id : null,
			'linked_chat_id'               => $this->linked_chat ? $this->linked_chat->id : null,
			'email_account_id'             => $this->email_account ? $this->email_account->id : null,
			'locked_by_agent'              => $this->locked_by_agent ? $this->locked_by_agent->id : null,
			'ref'                          => $this->ref,
			'auth'                         => $this->auth,
			'sent_to_address'              => $this->sent_to_address,
			'creation_system'              => $this->creation_system,
			'creation_system_option'       => $this->creation_system_option,
			'ticket_hash'                  => $this->ticket_hash,
			'status'                       => $this->status,
			'hidden_status'                => $this->hidden_status,
			'validating'                   => $this->validating,
			'is_hold'                      => $this->is_hold,
			'urgency'                      => $this->urgency,
			'count_agent_replies'          => $this->count_agent_replies,
			'count_user_replies'           => $this->count_user_replies,
			'feedback_rating'              => $this->feedback_rating,
			'date_feedback_rating'         => $this->date_feedback_rating ? $this->date_feedback_rating->format('Y-m-d H:i:s') : null,
			'date_created'                 => $this->date_created->format('Y-m-d H:i:s'),
			'date_resolved'                => $this->date_resolved ? $this->date_resolved->format('Y-m-d H:i:s') : null,
			'date_closed'                  => $this->date_closed ? $this->date_closed->format('Y-m-d H:i:s') : null,
			'date_first_agent_assign'      => $this->date_first_agent_assign ? $this->date_first_agent_assign->format('Y-m-d H:i:s') : null,
			'date_first_agent_reply'       => $this->date_first_agent_reply ? $this->date_first_agent_reply->format('Y-m-d H:i:s') : null,
			'date_last_agent_reply'        => $this->date_last_agent_reply ? $this->date_last_agent_reply->format('Y-m-d H:i:s') : null,
			'date_last_user_reply'         => $this->date_last_user_reply ? $this->date_last_user_reply->format('Y-m-d H:i:s') : null,
			'date_last_user_reply'         => $this->date_last_user_reply ? $this->date_last_user_reply->format('Y-m-d H:i:s') : null,
			'date_agent_waiting'           => $this->date_agent_waiting ? $this->date_agent_waiting->format('Y-m-d H:i:s') : null,
			'date_user_waiting'            => $this->date_user_waiting ? $this->date_user_waiting->format('Y-m-d H:i:s') : null,
			'date_status'                  => $this->date_status->format('Y-m-d H:i:s'),
			'total_user_waiting'           => $this->total_user_waiting,
			'total_to_first_reply'         => $this->total_to_first_reply,
			'date_locked'                  => $this->date_locked ? $this->date_locked->format('Y-m-d H:i:s') : null,
			'has_attachments'              => $this->has_attachments,
			'subject'                      => $this->subject,
			'original_subject'             => $this->original_subject,
			'properties'                   => $this->properties ? serialize($this->properties) : null,
			'worst_sla_status'             => $this->worst_sla_status,
			'waiting_times'                => $this->waiting_times ? serialize($this->waiting_times) : null,
		);

		return $row_data;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\StateChangeRecorder
	 */
	public function getStateChangeRecorder()
	{
		//This is overridden just so phpcod returns proper subclass
		return parent::getStateChangeRecorder();
	}


	/**
	 * @return TicketChangeTracker
	 */
	public function getTicketLogger()
	{
		if ($this->__dp_ticket_change_tracker) {
			return $this->__dp_ticket_change_tracker;
		}

		$this->__dp_ticket_change_tracker = new TicketChangeTracker($this);
		return $this->__dp_ticket_change_tracker;
	}

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights)
    {
        if (!empty($highlights)) {
            $this->_search_highlights = $highlights;
        }
    }

    /**
     * Get Elasticsearch highlight data
     *
     * @param null $field
     * @return array|null
     */
    public function getElasticHighlights($field = null)
    {
        if (is_null($field)) {
            return $this->_search_highlights;
        } else {
            if (isset($this->_search_highlights[$field])) {
                return $this->_search_highlights[$field];
            } else {
                return null;
            }
        }
    }

	############################################################################
	# Doctrine Metadata
	############################################################################

	public function _setOriginalId()
	{
		$this->_original_id = $this->id;
		$this->__dp_auto_ticket_process = true;
	}

	public function _autoProcessTicket()
	{
		if ($this->__dp_is_processing_ticket) {
			return;
		}

		if ($this->__dp_auto_ticket_process) {

			// Detect when we last did a save
			if ($this->__dp_last_process_save) {
				$state = $this->getStateChangeRecorder();
				if ($state->getStateVersion() <= $this->__dp_last_process_save) {
					return;
				}
			}

			$tm = App::$container->getTicketManager();

			$context = new ExecutorContext();
			if (App::getCurrentPerson()) {
				$context->setPersonContext(App::getCurrentPerson(), true);
			}

			$state = $this->getStateChangeRecorder();
			if ($state->isNewTicket()) {
				$event_type = 'newticket';
			} elseif ($state->hasNewReply()) {
				$event_type = 'newreply';
			} else {
				$event_type = 'update';
			}

			if (defined('DP_INTERFACE')) {
				$person = App::getCurrentPerson();

				if (!$person || !$person->id) {
					$person = null;
				}

				switch (DP_INTERFACE) {
					case 'admin':
					case 'agent':
						$context = $tm->createAgentExecutorContext(
							$person,
							$event_type,
							'web'
						);
						break;
					case 'user':
						if (!$person && $this->person) {
							$person = $this->person;
						}
						$context = $tm->createUserExecutorContext(
							$person,
							$event_type,
							'portal'
						);
						break;
					case 'api':
						$context = $tm->createAgentExecutorContext(
							$person,
							$event_type,
							'api'
						);
						break;
					default:
						$context = $tm->createSystemExecutorContext();
						break;
				}
			}

			$this->__dp_is_processing_ticket = true;

			try {
				$tm->saveTicket($this, $context);
				$this->__dp_is_processing_ticket = false;
			} catch (\Exception $e) {
				$this->__dp_is_processing_ticket = false;
				throw $e;
			}
		}
	}

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Ticket';
		$metadata->addLifecycleCallback('_setOriginalId', 'postLoad');
		$metadata->addLifecycleCallback('_autoProcessTicket', 'postPersist');
		$metadata->addLifecycleCallback('_autoProcessTicket', 'postUpdate');
		$metadata->setPrimaryTable(array(
			'name'    => 'tickets',
			'indexes' => array(
				'date_created_idx' => array('columns' => array('date_created')),
				'date_locked_idx'  => array('columns' => array('date_locked')),
				'status_idx'       => array('columns' => array('status')),
			),
			'uniqueConstraints' => array(
				'ref_idx' => array('columns' => array('ref'))
			)
		));

		$metadata->mapField(array(
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'id'         => true,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'ref',
			'fieldName'  => 'ref',
			'type'       => 'string',
			'length'     => 100,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'auth',
			'fieldName'  => 'auth',
			'type'       => 'string',
			'length'     => 20,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'sent_to_address',
			'fieldName'  => 'sent_to_address',
			'type'       => 'string',
			'length'     => 200,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'email_account_address',
			'fieldName'  => 'email_account_address',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'creation_system',
			'fieldName'  => 'creation_system',
			'type'       => 'string',
			'length'     => 100,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'creation_system_option',
			'fieldName'  => 'creation_system_option',
			'type'       => 'string',
			'length'     => 1000,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'ticket_hash',
			'columnName' => 'ticket_hash',
			'type'       => 'string',
			'length'     => 40,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'status',
			'columnName' => 'status',
			'type'       => 'string',
			'length'     => 30,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'hidden_status',
			'columnName' => 'hidden_status',
			'type'       => 'string',
			'length'     => 30,
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'validating',
			'columnName' => 'validating',
			'type'       => 'string',
			'length'     => 35,
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'is_hold',
			'columnName' => 'is_hold',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'urgency',
			'columnName' => 'urgency',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'count_agent_replies',
			'columnName' => 'count_agent_replies',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'count_user_replies',
			'columnName' => 'count_user_replies',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'feedback_rating',
			'columnName' => 'feedback_rating',
			'type'       => 'integer',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_feedback_rating',
			'columnName' => 'date_feedback_rating',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_created',
			'columnName' => 'date_created',
			'type'       => 'datetime',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_resolved',
			'columnName' => 'date_resolved',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_closed',
			'columnName' => 'date_closed',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_first_agent_assign',
			'columnName' => 'date_first_agent_assign',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_first_agent_reply',
			'columnName' => 'date_first_agent_reply',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_last_agent_reply',
			'columnName' => 'date_last_agent_reply',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_last_user_reply',
			'columnName' => 'date_last_user_reply',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_agent_waiting',
			'columnName' => 'date_agent_waiting',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_user_waiting',
			'columnName' => 'date_user_waiting',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_status',
			'columnName' => 'date_status',
			'type'       => 'datetime',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'total_user_waiting',
			'columnName' => 'total_user_waiting',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'total_to_first_reply',
			'columnName' => 'total_to_first_reply',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_locked',
			'columnName' => 'date_locked',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'has_attachments',
			'columnName' => 'has_attachments',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'subject',
			'columnName' => 'subject',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'original_subject',
			'columnName' => 'original_subject',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'properties',
			'columnName' => 'properties',
			'type'       => 'array',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'worst_sla_status',
			'columnName' => 'worst_sla_status',
			'type'       => 'string',
			'length'     => 20,
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'waiting_times',
			'columnName' => 'waiting_times',
			'type'       => 'array',
			'nullable'   => true,
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'parent_ticket',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
			'joinColumns'  => array(array(
				'name'                 => 'parent_ticket_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null'
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'language',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Language',
			'joinColumns'          => array(array(
				'name'                 => 'language_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'department',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Department',
			'joinColumns'          => array(array(
				'name'                 => 'department_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'category',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketCategory',
			'joinColumns'          => array(array(
				'name'                 => 'category_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'priority',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketPriority',
			'joinColumns'          => array(array(
				'name'                 => 'priority_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'workflow',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketWorkflow',
			'joinColumns'          => array(array(
				'name'                 => 'workflow_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'product',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Product',
			'joinColumns'          => array(array(
				'name'                 => 'product_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'person',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Person',
			'joinColumns'          => array(array(
				'name'                 => 'person_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
			)),
			'dpApi'                => true,
			'dpApiDeep'            => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'person_email',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\PersonEmail',
			'joinColumns'          => array(array(
				'name'                 => 'person_email_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true,
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'person_email_validating',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\PersonEmailValidating',
			'joinColumns'          => array(array(
				'name'                 => 'person_email_validating_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true,
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'agent',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Person',
			'joinColumns'          => array(array(
				'name'                 => 'agent_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'agent_team',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\AgentTeam',
			'joinColumns'          => array(array(
				'name'                 => 'agent_team_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'organization',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Organization',
			'joinColumns'          => array(array(
				'name'                 => 'organization_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'linked_chat',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\ChatConversation',
			'joinColumns'          => array(array(
				'name'                 => 'linked_chat_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'attachments',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketAttachment',
			'cascade'              => array('remove', 'persist', 'merge', ),
			'mappedBy'             => 'ticket',
			'fetch'                => 'EXTRA_LAZY',
			'dpApi'                => true,
			'dpApiDeep'            => true
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'access_codes',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketAccessCode',
			'cascade'              => array('persist', 'merge'),
			'mappedBy'             => 'ticket',
			'onDelete'             => 'cascade'
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'messages',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketMessage',
			'cascade'              => array('remove', 'persist', 'merge'),
			'mappedBy'             => 'ticket',
			'fetch'                => 'EXTRA_LAZY',
			'orderBy'              => array( 'date_created' => 'ASC'),
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'custom_data',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\CustomDataTicket',
			'cascade'              => array('remove', 'persist', 'merge'),
			'mappedBy'             => 'ticket',
			'orphanRemoval'        => true,
			'dpApi'                => true
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'labels',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\LabelTicket',
			'cascade'              => array('remove', 'persist', 'merge'),
			'mappedBy'             => 'ticket',
			'orphanRemoval'        => true
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'email_account',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\EmailAccount',
			'joinColumns'          => array(array(
				'name'                 => 'email_account_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
		));
		$metadata->mapManyToOne(array(
			'fieldName'            => 'locked_by_agent',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Person',
			'joinColumns'          => array(array(
				'name'                => 'locked_by_agent',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
				'columnDefinition'     => NULL
			)),
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'participants',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketParticipant',
			'cascade'              => array('remove', 'persist', 'merge'),
			'mappedBy'             => 'ticket',
			'orphanRemoval'        => true,
			'dpApi'                => true,
			'dpApiDeep'            => true
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'charges',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketCharge',
			'cascade'              => array('remove', 'persist', 'merge'),
			'mappedBy'             => 'ticket',
			'orphanRemoval'        => true,
			'dpApi'                => true,
			'dpApiDeep'            => true
		));
		$metadata->mapOneToMany(array(
			'fieldName'            => 'ticket_slas',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\TicketSla',
			'cascade'              => array('persist', 'merge'),
			'mappedBy'             => 'ticket',
			'orphanRemoval'        => true,
			'dpApi'                => true,
			'dpApiDeep'            => true
		));
	}
}
