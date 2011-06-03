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
	/**
	 * @var int
	 * @orm:Id
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="subject", type="string", length=255)
	 */
	protected $subject = '';

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Person", cascade={"all"})
     * @orm:JoinTable(name="chat_conversation_to_person", joinColumns={@orm:JoinColumn(name="conversation_id", referencedColumnName="id")}, inverseJoinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")})
	 */
	protected $participants;

	/**
	 * Is this an agent chat
	 *
	 * @var bool
	 * @orm:Column(name="is_agent", type="boolean")
	 */
	protected $is_agent = true;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	protected $_user_participants = null;

	public function __construct()
	{
		$this->participants = new \Doctrine\Common\Collections\ArrayCollection();
		$this->date_created = new \DateTime();
	}

	public function createMessage($message, $author)
	{
		$chat_message = new ChatMessage();
		$chat_message->conversation = $this;
		$chat_message->author = $author;
		$chat_message['content'] = $message;

		return $chat_message;
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
}