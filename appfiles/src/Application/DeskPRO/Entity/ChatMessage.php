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

use Orb\Util\Strings;

/**
 * Basic hierarchicial category entity. Hierarchy is maintained automatically
 * by a Doctrine NestedSet implementation
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="chat_messages")
 */
class ChatMessage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The conversation the message belongs to
	 * @var \Application\DeskPRO\Entity\Conversation
	 * @ORM_Mapping\ManyToOne(targetEntity="ChatConversation", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $conversation;

	/**
	 * Person who created the message
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="author_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $author = null;

	/**
	 * The authors name at the point of this message
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="person_name", type="string", length=255)
	 */
	protected $person_name = '';

	/**
	 * The message
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * Is this a system message? (ended, joined, etc)
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_sys", type="boolean")
	 */
	protected $is_sys = false;

	/**
	 * Is the message hidden from the user?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_user_hidden", type="boolean")
	 */
	protected $is_user_hidden = false;

	/**
	 * Is the content an HTML message?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_html", type="boolean")
	 */
	protected $is_html = false;

	/**
	 * Data
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="metadata", type="array")
	 */
	protected $metadata = array();

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function setAuthor($author)
	{
		// Could be a guest, in which case we dont care
		if ($author && $author->id) {
			$this->author = $author;
			if ($author) {
				$this->person_name = $author->getDisplayName();
			}
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
	 * @ORM_Mapping\PrePersist
	 */
	public function _setUserName()
	{
		// If we have no name, then assume the message is
		// by the user who started the chat
		if (!$this->person_name) {
			$this->person_name = $this->conversation['person_name'];
		}
	}

	/**
	 * Get a basic array of message information. These are generally used in templates or with
	 * client messages to render the message.
	 *
	 * @return array
	 */
	public function getInfo()
	{
		$info = array();

		$info['conversation_id'] = $this->conversation->id;
		$info['message_id'] = $this->id;

		if ($this->is_sys) {
			$info['author_id'] = 0;
			$info['author_name'] = '*';
			$info['author_type'] = 'sys';
		} elseif ($this->author) {
			$info['author_id'] = $this->author->id;
			$info['author_name'] = $this->author->display_name;
			$info['author_type'] = $this->author->is_agent ? 'agent' : 'user';
		} else {
			$info['author_id'] = 0;
			$info['author_name'] = $this->getAuthorName();
			$info['author_type'] = 'user';
		}

		$info['content']       = $this->content;
		$info['is_html']       = $this->is_html;
		$info['metadata']      = $this->metadata;
		$info['date_created']  = $this->date_created->getTimestamp();

		return $info;
	}
}
