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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;
use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

/**
 * A conversation between one or more people
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ChatConversation")
 * @ORM_Mapping\Table(name="chat_conversations")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class ChatConversation extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_OPEN  = 'open';
	const STATUS_ENDED = 'ended';

	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @ORM_Mapping\ManyToOne(targetEntity="Department", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="department_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $department = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="subject", type="string", length=255)
	 */
	protected $subject = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="status", type="string", length=15)
	 */
	protected $status = 'open';

	/**
	 * If this is a user conversation, this is the agent assigned.
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $agent = null;

	/**
	 * If this is a team chat, the team it is
	 *
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @ORM_Mapping\ManyToOne(targetEntity="AgentTeam", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $agent_team = null;

	/**
	 * If this is a user conversation, this is the user who started the chat
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * If this is a user convo, this is the users session
	 *
	 * @var \Application\DeskPRO\Entity\Session
	 * @ORM_Mapping\ManyToOne(targetEntity="Session")
	 * @ORM_Mapping\JoinColumn(name="session_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $session = null;

	/**
	 * User chat: The users name, if they arent a person
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="person_name", type="string", length=255)
	 */
	protected $person_name = '';

	/**
	 * User chat: The users email, if they arent a person
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="person_email", type="string", length=255)
	 */
	protected $person_email = '';

	/**
	 * ...and this is the users visitor
	 *
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @ORM_Mapping\ManyToOne(targetEntity="Visitor")
	 * @ORM_Mapping\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Person", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="chat_conversation_to_person", joinColumns={@ORM_Mapping\JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $participants;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="ChatMessage", mappedBy="conversation", cascade={"persist", "remove", "merge"})
	 */
	protected $messages;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="rating_response_time", type="integer", nullable=true)
	 */
	protected $rating_response_time = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="rating_overall", type="integer", nullable=true)
	 */
	protected $rating_overall = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="rating_comment", type="text")
	 */
	protected $rating_comment = '';

	/**
	 * Is this an agent chat
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent", type="boolean")
	 */
	protected $is_agent = false;

	/**
	 * If the chat is popped out into a window.
	 * This is used to make sure the JS widget on pages doesn't load again.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_window", type="boolean")
	 */
	protected $is_window = false;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_assigned",type="datetime",nullable=true)
	 */
	protected $date_assigned;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_first_agent_message",type="datetime",nullable=true)
	 */
	protected $date_first_agent_message;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_ended",type="datetime",nullable=true)
	 */
	protected $date_ended;

	protected $_created_messages = array();

	protected $_user_participants = null;

	public function getChannelId($name = false)
	{
		return 'chat_convo.' . $this->id . ($name ? '.' . $name : '');
	}

	/**
	 * @static
	 * @param \Application\DeskPRO\Entity\Session $session
	 * @return \Application\DeskPRO\Entity\ChatConversation
	 */
	public static function newForUserSession($session)
	{
		$convo = new self();
		if ($session->person) {
			$convo->person = $session->person;
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
	public function addNewMessage($content, $author, $is_html = false)
	{
		$chat_message = new ChatMessage();
		$chat_message->conversation = $this;
		$chat_message->author = $author;
		$chat_message['content'] = $content;

		if ($is_html) {
			$chat_message['is_html'] = true;
		}

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

		$message->conversation = $this;
		$this->messages->add($message);

		$this->_created_messages[] = $message;

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
	public function addParticipant($person_or_id, $suppress_sys_msg = false)
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
	public function removeParticipant($person_or_id, $suppress_sys_msg = false)
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

		// Make sure the user isnt both assigned and a part
		if ($agent) {
			$this->removeParticipant($agent, true);
		}

		// Automatically add old assigned guy as part
		if ($old_agent) {
			$this->addParticipant($old_agent, true);
		}
	}

	public function getAgentId()
	{
		if ($this->agent) {
			return $this->agent->id;
		}

		return 0;
	}

	public function getDepartmentId()
	{
		if ($this->department) {
			return $this->department->id;
		}

		return 0;
	}

	public function getCreatedMessages()
	{
		return $this->_created_messages;
	}

	public function _clearCreatedMessages()
	{
		$this->_created_messages = array();
	}

	public function getSubjectLine()
	{
		if ($this->subject) {
			return $this->subject;
		}

		// TODO this needs to be improved so it doesnt load
		// the whole messages graph

		$line = '';

		foreach ($this->messages as $message) {
			if ($message->is_sys) continue;
			if ($message->author && $message->author->is_agent) continue;

			if ($message->is_html) {
				$line .= strip_tags($message->content);
			} else {
				$line .= $message->content;
			}
			if (strlen($line) >= 190) {
				continue;
			}
		}

		if (!$line) {
			$line = 'Chat ' . $this->id;
		}

		$line = substr($line, 0, 190);

		return $line;
	}

	public function setRatingOverall($rating)
	{
		$this->rating_overall = Numbers::bound($rating, 1, 5);
	}

	public function setRatingResponseTime($rating)
	{
		$this->rating_response_time = Numbers::bound($rating, 1, 5);
	}

	/**
	 * Get a basic array of information. These are generally used in templates or with
	 * client messages to render the message.
	 *
	 * @return array
	 */
	public function getInfo()
	{
		$info = array();

		$info['conversation_id'] = $this->id;

		if ($this->person) {
			$info['author_id']     = $this->person->id;
			$info['author_name']   = $this->person->display_name;
			$info['author_email']  = $this->person->getPrimaryEmailAddress();
			$info['author_type']   = $this->person->is_agent ? 'agent' : 'user';
		} else {
			$info['author_id']     = 0;
			$info['author_name']   = $this->person_name ? $this->person_name : '';
			$info['author_email']  = $this->person_email ? $this->person_email : '';
			$info['author_type']   = 'user';
		}

		$info['subject_line']     = $this->getSubjectLine();
		$info['agent_id']         = $this->agent ? $this->agent->id : 0;
		$info['agent_name']       = $this->agent ? $this->agent->getDisplayName() : '';
		$info['department_id']    = $this->department_id;
		$info['department_name']  = $this->department ? $this->department->getFullTitle() : '';
		$info['date_created']     = $this->date_created->getTimestamp();

		return $info;
	}

	/**
	 * @ORM_Mapping\PostUpdate
	 */
	public function _queueSearchIndexUpdate($op = 'update')
	{
		$container = App::getContainer();
		if ($container instanceof \Application\DeskPRO\DependencyInjection\DeskproContainer) {
			$container->getSystemService('search_indexer')->update($this, $op);
		}
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ChatConversation'; 
		$metadata->setPrimaryTable(array( 'name' => 'chat_conversations', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->addLifecycleCallback('_queueSearchIndexUpdate', 'postUpdate'); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'subject', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'subject', )); 
		$metadata->mapField(array( 'fieldName' => 'status', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'status', )); 
		$metadata->mapField(array( 'fieldName' => 'person_name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'person_name', )); 
		$metadata->mapField(array( 'fieldName' => 'person_email', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'person_email', )); 
		$metadata->mapField(array( 'fieldName' => 'rating_response_time', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'rating_response_time', )); 
		$metadata->mapField(array( 'fieldName' => 'rating_overall', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'rating_overall', )); 
		$metadata->mapField(array( 'fieldName' => 'rating_comment', 'type' => 'text', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'rating_comment', )); 
		$metadata->mapField(array( 'fieldName' => 'is_agent', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_agent', )); 
		$metadata->mapField(array( 'fieldName' => 'is_window', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_window', )); 
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'date_created', )); 
		$metadata->mapField(array( 'fieldName' => 'date_assigned', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'date_assigned', )); 
		$metadata->mapField(array( 'fieldName' => 'date_first_agent_message', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'date_first_agent_message', )); 
		$metadata->mapField(array( 'fieldName' => 'date_ended', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'date_ended', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'department', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Department', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'department_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'agent', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'agent_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'agent_team', 'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentTeam', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'agent_team_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'session', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Session', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'session_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'visitor', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Visitor', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'visitor_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapManyToMany(array( 'fieldName' => 'participants', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( 0 => 'remove', 1 => 'persist', 3 => 'merge', ), 'joinTable' => array( 'name' => 'chat_conversation_to_person', 'schema' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'conversation_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'inverseJoinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), ), )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'messages', 'targetEntity' => 'Application\\DeskPRO\\Entity\\ChatMessage', 'cascade' => array( 0 => 'remove', 1 => 'persist', 3 => 'merge', ), 'mappedBy' => 'conversation', 'orphanRemoval' => false, ));
	}
}

