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

	const HIDDEN_STATUS_SPAM = 'spam';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The language associate with the user.
	 *
	 * @var \Application\DeskPRO\Entity\Language
	 * @orm:ManyToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language = null;

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
	 * @var \Application\DeskPRO\Entity\Product
	 * @orm:ManyToOne(targetEntity="Product")
	 * @orm:JoinColumn(name="product_id", referencedColumnName="id")
	 */
	protected $product = null;

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var int
	 * @orm:Column(name="agent_id", type="integer", nullable=true)
	 */
	protected $agent_id = null;

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
	 * @orm:OneToMany(targetEntity="TicketMessage", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $messages;

	/**
	 * @orm:OneToMany(targetEntity="CustomDataTicket", mappedBy="ticket", cascade={"persist", "remove", "merge"})
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
	 * @var int
	 * @orm:Column(name="locked_by_agent", type="integer")
	 */
	protected $locked_by_agent = 0;

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
	 * @orm:OneToMany(targetEntity="TicketParticipant", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $participants;

	/**
	 * Ticket logger
	 * @var Application\DeskPRO\Tickets\TicketLogListener
	 */
	protected $_ticket_logger;

	protected $_label_manager = null;

	public function __construct()
	{
		$this->participants = new \Doctrine\Common\Collections\ArrayCollection();
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();
		$this->custom_data = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();

		$this->date_created = new \DateTime();

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
			$ids[] = $p['person_id'];
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
			if ($p['person_id'] == $person_id) {
				return true;
			}
		}

		return false;
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
	public function setCustomData($field_id, $value)
	{
		$custom_data = $this->getCustomDataForField($field_id);
		if (!$custom_data) {
			if ($value === null) return null;
			$field = App::getApi('custom_fields.tickets')->getFieldFromId($field_id);
			if (!$field) {
				throw new \Exception("Invalid field_id `$field_id`");
			}
			$custom_data = $field->createNewDataObject();
		}

		if ($value === null) {
			$this['custom_data']->removeElement($custom_data);
			return null;
		}

		$custom_data->setData($value);
		$this->addCustomData($custom_data);

		return $custom_data;
	}


	/**
	 * Add a label
	 * @param Entity\LabelTicket $label
	 */
	public function addLabel(Entity\LabelTicket $label)
	{
		$label['ticket'] = $this;
		$this->labels->add($label);
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

	public function getPersonId()
	{
		return $this->person['id'];
	}

	public function setPersonId($id)
	{
		$person = App::getOrm()->getRepository('DeskPRO:Person')->find($id);
		$this['person'] = $person;
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
			$this->product = $prod;
		} else {
			$this->product = null;
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
			$this->priority = $pri;
		} else {
			$this->priority = null;
		}
	}

	public function setAgent(Entity\Person $agent = null)
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

			$this->agent = $agent;
		} else {
			$this->agent = null;
		}
	}
	public function setAgentTeam(Entity\AgentTeam $agent_team = null)
	{
		$this->agent_team = $agent_team;
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
			$this->agent_team = $agent_team;
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
	 * @orm:PreInsert
	 */
	public function _preInsert()
	{
		if ($this->_ticket_logger) {
			$action = new \Application\DeskPRO\Tickets\Actions\Created($this);
			$this->_ticket_logger->logAction($action);
		}
	}

	/**
	 * @orm:PostUpdate
	 * @orm:PostInsert
	 */
	public function _saveTicketLogs()
	{
		if ($this->_ticket_logger) {
			$this->_ticket_logger->saveLogs();
		}
	}

	public function getTicketLogger()
	{
		$this->_ticket_logger;
	}


	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelTicket');
		}

		return $this->_label_manager;
	}
}