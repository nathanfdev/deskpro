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

use Orb\Util\Strings;

/**
 * A conversation between one or more people
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ChatConversation")
 * @orm:Table(name="chat_conversations")
 */
class ChatConversation extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_OPEN  = 'open';
	const STATUS_ENDED = 'ended';

	/**
	 * @var int
	 * @orm:Id
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department", fetch="EAGER")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * @var string
	 * @orm:Column(name="subject", type="string", length=255)
	 */
	protected $subject = '';

	/**
	 * @var string
	 * @orm:Column(name="status", type="string", length=15)
	 */
	protected $status = 'open';

	/**
	 * If this is a user conversation, this is the agent assigned.
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="agent_id", referencedColumnName="id")
	 */
	protected $agent = null;

	/**
	 * If this is a user conversation, this is the user who started the chat
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * If this is a user convo, this is the users session
	 *
	 * @var \Application\DeskPRO\Entity\Session
	 * @orm:ManyToOne(targetEntity="Session")
	 * @orm:JoinColumn(name="session_id", referencedColumnName="id")
	 */
	protected $session = null;

	/**
	 * User chat: The users name, if they arent a person
	 * 
	 * @var string
	 * @orm:Column(name="person_name", type="string", length=255)
	 */
	protected $person_name = '';

	/**
	 * User chat: The users email, if they arent a person
	 *
	 * @var string
	 * @orm:Column(name="person_email", type="string", length=255)
	 */
	protected $person_email = '';

	/**
	 * ...and this is the users visitor
	 *
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @orm:ManyToOne(targetEntity="Visitor")
	 * @orm:JoinColumn(name="visitor_id", referencedColumnName="id")
	 */
	protected $visitor = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Person", cascade={"all"})
     * @orm:JoinTable(name="chat_conversation_to_person", joinColumns={@orm:JoinColumn(name="conversation_id", referencedColumnName="id")}, inverseJoinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")})
	 */
	protected $participants;

	/**
	 * @orm:OneToMany(targetEntity="ChatMessage", mappedBy="conversation", cascade={"persist", "remove", "merge"})
	 */
	protected $messages;

	/**
	 * Is this an agent chat
	 *
	 * @var bool
	 * @orm:Column(name="is_agent", type="boolean")
	 */
	protected $is_agent = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_assigned",type="datetime",nullable=true)
	 */
	protected $date_assigned;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_first_agent_message",type="datetime",nullable=true)
	 */
	protected $date_first_agent_message;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_ended",type="datetime",nullable=true)
	 */
	protected $date_ended;

	protected $_user_participants = null;

	/**
	 * @static
	 * @param \Application\DeskPRO\Entity\Session $session
	 * @return \Application\DeskPRO\Entity\ChatConversation
	 */
	public static function newForUserSession($session)
	{
		$convo = new self();
		if ($session->person) {
			$convo->person = $session;
		}
		$convo->session = $session;
		$convo->visitor = $session->visitor;

		return $convo;
	}

	public function __construct()
	{
		$this->participants = new \Doctrine\Common\Collections\ArrayCollection();
		$this->date_created = new \DateTime();

		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * Create a new message and then add it to this convo
	 */
	public function addNewMessage($content, $author)
	{
		$chat_message = new ChatMessage();
		$chat_message->conversation = $this;
		$chat_message->author = $author;
		$chat_message['content'] = $content;

		return $this->addMessage($chat_message);
	}
	


	/**
	 * Create a new message for a user based on their session
	 *
	 * @return \Application\DeskPRO\Entity\ChatMessage
	 */
	public function addNewMessageForSession($content, $session)
	{
		$chat_message = new ChatMessage();
		$chat_message->conversation = $this;

		if ($session->person) {
			$chat_message->author = $session->person;
		}

		$chat_message['content'] = $content;

		return $this->addMessage($chat_message);
	}


	/**
	 * Add a system message
	 *
	 * @return \Application\DeskPRO\Entity\ChatMessage
	 */
	public function addSystemMessage($content, $is_user_hidden = false)
	{
		$chat_message = new ChatMessage();
		$chat_message->conversation = $this;
		$chat_message['is_sys'] = true;
		$chat_message['is_user_hidden'] = $is_user_hidden;

		$chat_message['content'] = $content;

		return $this->addMessage($chat_message);
	}

	
	/**
	 * Add a message to this convo
	 *
	 * @param 
	 */
	public function addMessage($message)
	{
		if (!$this->date_first_agent_message AND $message->author AND $message->author['is_agent']) {
			$this['date_first_agent_message'] = new \DateTime();
		}

		if (!$this->subject) {
			if ($message->author AND $message->author['is_agent']) {
				if ($this->is_agent) {
					$this->subject = substr($message['content'], 0, 45);
				}
			} else {
				$this->subject = substr($message['content'], 0, 45);
			}
		}
		
		$message->conversation = $this;
		$this->messages->add($message);

		return $message;
	}


	/**
	 * Get an array of only user participants
	 * 
	 * @return array
	 */
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


	/**
	 * Get a simple array of person ID's of participants.
	 *
	 * @return array
	 */
	public function getParticipantIds()
	{
		$ids = array();
		foreach ($this->participants as $p) {
			$ids[] = $p['id'];
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
			if ($p['id'] == $person_id) {
				return $p;
			}
		}

		return false;
	}



	/**
	 * Add a participant
	 *
	 * @param $person_or_id
	 * @return Person
	 */
	public function addParticipant($person_or_id)
	{
		$person = $person_or_id;
		if (!($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		if ($this->hasParticipant($person)) {
			return $person;
		}

		$this->participants->add($person);

		if ($this->_user_participants !== null AND !$person['is_agent']) {
			$this->_user_participants[] = $person;
		}

		return $person;
	}



	/**
	 * Remove a participant
	 *
	 * @param  $person_or_id
	 * @return Person
	 */
	public function removeParticipant($person_or_id)
	{
		$person = $person_or_id;
		if (!($person instanceof Person)) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person);
		}

		foreach ($this->participants as $k => $p) {
			if ($p['id'] == $person['id']) {
				$this->participants->remove($k);
				return $p;
			}
		}

		return null;
	}


	/**
	 * Set the status (open or ended).
	 * 
	 * @param  $status
	 * @return void
	 */
	public function setStatus($status)
	{
		if ($this->status == $status) {
			return;
		}
		
		$this->_onPropertyChanged('status', $this->status, $status);
		$this->status = $status;
		
		if ($status == self::STATUS_ENDED) {
			if (!$this->date_ended) {
				$this['date_ended'] = new \DateTime();
			}

			$this->addSystemMessage(App::getTranslator()->phrase('core_chat.msg_ended'));

		} else {
			if ($this->date_ended) {
				$this['date_ended'] = null;
			}
		}
	}


	/**
	 * Set the agent
	 * 
	 * @param  $agent
	 * @return void
	 */
	public function setAgent($agent)
	{
		if (!$agent) $agent = null;
		
		$old_agent = $this->agent;
		if ($this->agent == $agent) {
			return;
		}
		
		$this->_onPropertyChanged('agent', $this->agent, $agent);
		
		$this->agent = $agent;
		if ($agent AND !$this->date_assigned) {
			$this['date_assigned'] = new \DateTime();
		}

		if ($old_agent) {
			$this->addSystemMessage(App::getTranslator()->phrase('core_chat.msg_unassigned_agent', array('agent_name '=> $agent['display_name'])));
		}

		if ($agent) {
			$this->addSystemMessage(App::getTranslator()->phrase('core_chat.msg_assigned_agent', array('agent_name '=> $agent['display_name'])));
		}
	}
}