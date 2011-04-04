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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Strings;

/**
 * Ticket
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Ticket")
 * @orm:Table(name="tickets")
 * @orm:HasLifecycleCallbacks
 */
class Ticket extends \Application\DeskPRO\Domain\DomainObject
{
	const CREATED_WEB_PERSON = 'web.person';
	const CREATED_WEB_AGENT = 'web.agent';
	const CREATED_GATEWAT_PERSON = 'gateway.person';

	const STATUS_OPEN = 'open';
	const STATUS_PENDING = 'pending';
	const STATUS_RESOLVED = 'resolved';
	const STATUS_CLOSED = 'closed';
	const STATUS_HIDDEN = 'hidden';

	const HIDDEN_STATUS_VALIDATING = 'validating';
	const HIDDEN_STATUS_SPAM = 'spam';
	const HIDDEN_STATUS_DELETED = 'deleted';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="ref", type="string", length=25)
	 */
	protected $ref = null;

	/**
	 * @var string
	 * @orm:Column(name="code", type="string", length=12)
	 */
	protected $code = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @orm:ManyToOne(targetEntity="TicketCategory")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketPriority
	 * @orm:ManyToOne(targetEntity="TicketPriority")
	 * @orm:JoinColumn(name="priority_id", referencedColumnName="id")
	 */
	protected $priority = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketWorkflow
	 * @orm:ManyToOne(targetEntity="TicketWorkflow")
	 * @orm:JoinColumn(name="workflow_id", referencedColumnName="id")
	 */
	protected $workflow = null;

	/**
	 * @var \Application\DeskPRO\Entity\Product
	 * @orm:ManyToOne(targetEntity="Product")
	 * @orm:JoinColumn(name="product_id", referencedColumnName="id")
	 */
	protected $product = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 * @orm:ManyToOne(targetEntity="PersonEmail")
	 * @orm:JoinColumn(name="person_email_id", referencedColumnName="id")
	 */
	protected $person_email = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="agent_id", referencedColumnName="id")
	 */
	protected $agent = null;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @orm:ManyToOne(targetEntity="AgentTeam")
	 * @orm:JoinColumn(name="agent_team_id", referencedColumnName="id")
	 */
	protected $agent_team = null;

	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization = null;

	/**
	 * @orm:OneToMany(targetEntity="TicketAttachment", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $attachments;

	/**
	 * @orm:OneToMany(targetEntity="TicketAccessCode", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $access_codes;

	/**
	 * @orm:OneToMany(targetEntity="TicketMessage", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $messages;

	/**
	 * @orm:OneToMany(targetEntity="CustomDataTicket", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;

	/**
	 * @orm:OneToMany(targetEntity="LabelTicket", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var string
	 * @orm:Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system;

	/**
	 * @TODO Make this an enum type
	 *
	 * @var string
	 * @orm:Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @TODO Make this an enum type
	 *
	 * @var string
	 * @orm:Column(name="hidden_status", type="string", length=15, nullable=true)
	 */
	protected $hidden_status = null;

	/**
	 * @var int
	 * @orm:Column(name="urgency", type="integer")
	 */
	protected $urgency = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_resolved",type="datetime",nullable=true)
	 */
	protected $date_resolved = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_closed",type="datetime",nullable=true)
	 */
	protected $date_closed = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_first_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_first_agent_reply = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_last_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_last_agent_reply = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_last_user_reply",type="datetime",nullable=true)
	 */
	protected $date_last_user_reply = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_agent_waiting",type="datetime",nullable=true)
	 */
	protected $date_agent_waiting = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_user_waiting",type="datetime",nullable=true)
	 */
	protected $date_user_waiting = null;

	/**
	 * @var int
	 * @orm:Column(name="total_user_waiting", type="integer")
	 */
	protected $total_user_waiting = 0;

	/**
	 * @var int
	 * @orm:Column(name="total_to_first_reply", type="integer")
	 */
	protected $total_to_first_reply = 0;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="locked_by_agent", referencedColumnName="id")
	 */
	protected $locked_by_agent = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_locked",type="datetime",nullable=true)
	 */
	protected $date_locked = null;

	/**
	 * @var bool
	 * @orm:Column(name="has_attachments", type="boolean")
	 */
	protected $has_attachments = false;

	/**
	 * @var string
	 * @orm:Column(name="subject", type="string", length=255)
	 */
	protected $subject;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TicketParticipant", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $participants;

	/**
	 * Ticket logger
	 * @var \Application\DeskPRO\Tickets\TicketLog\Logger
	 */
	protected $_ticket_logger;

	protected $_label_manager = null;
	
	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TaskAssociatedTicket", mappedBy="ticket")
	 */
	protected $task_associations;
	

	public function __construct()
	{
		$this->participants = new \Doctrine\Common\Collections\ArrayCollection();
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();
		$this->custom_data = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
		$this->task_associations = new \Doctrine\Common\Collections\ArrayCollection();

		$this->date_created = new \DateTime();

		$this->code = Strings::random(12, Strings::CHARS_KEY);
		$this->ref = Strings::random(12, Strings::CHARS_KEY);

		$this->_initTicketLogger();
	}

	/**
	 * @orm:PostLoad
	 */
	public function _initTicketLogger()
	{
		// Automatically create a ticket logger
		$ticket_logger = new \Application\DeskPRO\Tickets\TicketLog\Logger($this);
		$this->_ticket_logger = $ticket_logger;
		$this->addPropertyChangedListener($ticket_logger);
	}


	/**
	 * Get a simple array of person ID's of participants.
	 *
	 * @return array
	 */
	public function getParticipantIds()
	{
		$ids = array();
		foreach ($this->participants as $p) {
			$ids[] = $p['person']['id'];
		}

		return $ids;
	}



	/**
	 * Check if a person ID or a person object is current a participant.
	 *
	 * @param  $person_or_id
	 * @return bool
	 */
	public function hasParticipant($person_or_id)
	{
		$person_id = $person_or_id;
		if ($person_or_id instanceof Person) {
			$person_id = $person_or_id['id'];
		}

		foreach ($this->participants as $p) {
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
	public function addParticipant($person_or_id)
	{
		$person = $person_or_id;
		if (!($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		if ($ticket_part = $this->hasParticipant($person)) {
			return $ticket_part;
		}

		$ticket_part = new TicketParticipant();
		$ticket_part['person'] = $person;
		$ticket_part['ticket'] = $this;
		$this->participants->add($ticket_part);

		return $ticket_part;
	}



	/**
	 * Remove a participant
	 *
	 * @param  $person_or_id
	 * @return null
	 */
	public function removeParticipant($person_or_id)
	{
		$person = $person_or_id;
		if (!($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		foreach ($this->participants as $k => $p) {
			if ($p['person']['id'] == $person['id']) {
				$this->participants->remove($k);
				return $p;
			}
		}

		return null;
	}



	/**
	 * Add a message to this ticket.
	 *
	 * @param TicketMessage $message
	 */
	public function addMessage(TicketMessage $message)
	{
		$this->messages->add($message);
		$message['ticket'] = $this;

		if ($message['person']['is_agent']) {
			$this['date_last_agent_reply'] = new \DateTime();
		} else {
			$this['date_last_user_reply'] = new \DateTime();
		}

		$this->_onPropertyChanged('messages', null, $message);
	}



	/**
	 * Find an existing data record for a field id.
	 *
	 * @param int $field_id
	 * @return CustomDataTicket
	 */
	public function getCustomDataForField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data['field_id'] == $field_id) {
				return $data;
			}
		}

		return null;
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

		if ($value === null) {
			$this['custom_data']->removeElement($custom_data);
			return null;
		}

		$custom_data[$value_type] = $value;

		if ($is_new) {
			$this->addCustomData($custom_data);
		}

		return $custom_data;
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
	 * Render a custom field
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

	public function getPersonEmail()
	{
		if ($this->person_email) {
			return $this->person_email;
		} else {
			return $this->person['primary_email'];
		}
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

	public function setDepartment(Department $dep)
	{
		$this->department = $dep;

		if (!$this->verifyDepartmentCategory()) {
			$this->category = null;
		}
	}

	public function verifyDepartmentCategory()
	{
		if (!$this->category) {
			return true;
		}

		$map = App::getEntityRepository('DeskPRO:TicketCategory')->departmentToCategoryMap();
		$valid_cat_ids = array();
		if (isset($map[$this->department['id']])) {
			$valid_cat_ids = $map[$this->department['id']];
		}

		if (!in_array($this->category['id'], $valid_cat_ids)) {
			return false;
		}

		return true;
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

	public function setCategory(TicketCategory $cat = null)
	{
		if (!$cat) {
			$this->category = null;
			return;
		}

		$this->category = $cat;
		if (!$this->verifyDepartmentCategory()) {
			$this->category = null;
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
		if (!$this->priority) {
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

	public function setAgent(Person $agent = null)
	{
		$this->agent = $agent;
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
				// TODO err
			}

			$this['agent'] = $agent;
		} else {
			$this['agent'] = null;
		}
	}
	public function setAgentTeam(AgentTeam $agent_team = null)
	{
		$this['agent_team'] = $agent_team;
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

	public function isLocked()
	{
		$lock_timeout = date_create('-' . App::getSetting('core_tickets.lock_timeout') . ' seconds');

		if (!$this->locked_by_agent) {
			return false;
		}

		// Timed out
		if ($this->date_locked < $lock_timeout) {
			return false;
		}

		// Check if we have a current user context,
		// to see if its locked to us
		$person = App::getCurrentPerson();
		if ($person AND $this->locked_by_agent['id'] == $person['id']) {
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
		try {
			$del = App::getOrm()->createQuery("
				SELECT d
				FROM DeskPRO:TicketDeleted d
				WHERE d.ticket_id = ?1
			")->setParameter(1, $this->id)->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

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



	public function setStatus($status)
	{
		$old_status = $this->status;
		$old_hstatus = $this->hidden_status;

		if ($status != 'hidden' AND $this->status == 'hidden' AND $this->hidden_status == 'deleted') {
			$this->undeleteTicket();
		}

		$this->status = $status;

		$this->_onPropertyChanged('status', $old_status, $this->status);

		if ($status != 'hidden') {
			$this->setHiddenStatus(null);
		}
	}

	public function setHiddenStatus($hstatus)
	{
		$old_hstatus = $this->hidden_status;

		$this->hidden_status = $hstatus;

		$this->_onPropertyChanged('hidden_status', $old_hstatus, $this->hidden_status);
	}



	/**
	 * Undelete a ticket.
	 *
	 * This will set the status to 'open' if it wasn't changed before.
	 */
	public function undeleteTicket()
	{
		$del = $this->getDeletionRecord();
		if (!$del) {
			return;
		}

		if ($this->status == 'hidden') {
			$this->status = self::STATUS_OPEN;
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

		if (!$person) {
			$person = App::getCurrentPerson();
		}

		$del['ticket_id']     = $this->id;
		$del['by_person_id']  = $person['id'];
		$del['new_ticket_id'] = 0;
		$del['reason']        = $reason;

		$this->status        = self::STATUS_HIDDEN;
		$this->hidden_status = self::HIDDEN_STATUS_DELETED;

		App::getOrm()->persist($del);
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

		App::getOrm()->persist($tac);
		App::getOrm()->flush();
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
	 * @orm:PrePersist
	 */
	public function _preInsert()
	{
		// Get the new ref
		$this->ref = App::getRefGenerator()->generateReference('DeskPRO:Ticket');

		if ($this->_ticket_logger) {
			$action = new \Application\DeskPRO\Tickets\TicketLog\Actions\Created($this);
			$this->_ticket_logger->logAction($action);
		}
	}

	/**
	 * @orm:PostUpdate
	 * @orm:PostPersist
	 */
	public function _saveTicketLogs()
	{
		if ($this->_ticket_logger) {
			$this->_ticket_logger->done();
		}
	}

	public function getTicketLogger()
	{
		return $this->_ticket_logger;
	}


	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelTicket');
		}

		return $this->_label_manager;
	}
}