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
 * Basic hierarchicial category entity. Hierarchy is maintained automatically
 * by a Doctrine NestedSet implementation
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="chat_messages")
 */
class ChatMessage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The conversation the message belongs to
	 * @var \Application\DeskPRO\Entity\Conversation
	 * @orm:ManyToOne(targetEntity="ChatConversation", fetch="EAGER")
	 * @orm:JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $conversation;

	/**
	 * Person who created the message
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="author_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $author = null;

	/**
	 * The authors name at the point of this message
	 *
	 * @var string
	 * @orm:Column(name="person_name", type="string", length=255)
	 */
	protected $person_name = '';

	/**
	 * The message
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * Is this a system message? (ended, joined, etc)
	 *
	 * @var bool
	 * @orm:Column(name="is_sys", type="boolean")
	 */
	protected $is_sys = false;

	/**
	 * Is the message hidden from the user?
	 *
	 * @var bool
	 * @orm:Column(name="is_user_hidden", type="boolean")
	 */
	protected $is_user_hidden = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function setAuthor($author)
	{
		$this->author = $author;
		if ($author) {
			$this->person_name = $author->getDisplayName();
		}
	}

	public function getAuthorId()
	{
		if ($this->author) {
			return $this->author['id'];
		}

		return 0;
	}

	public function getAuthorName()
	{
		if ($this->is_sys) {
			return '*';
		} elseif ($this->author) {
			return $this->author['display_name'];
		} else if ($this->conversation['person_name']) {
			return $this->conversation['person_name'];
		}

		return 'User';
	}

	/**
	 * @orm:PrePersist
	 */
	public function _setUserName()
	{
		// If we have no name, then assume the message is
		// by the user who started the chat
		if (!$this->person_name) {
			$this->person_name = $this->conversation['person_name'];
		}
	}
}