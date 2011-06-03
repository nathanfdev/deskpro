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
 * @orm:Table(name="chat_messages")
 */
class ChatMessage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id
	 * @orm:Column(name="id", type="string", length=100)
	 */
	protected $id = null;

	/**
	 * The conversation the message belongs to
	 * @var \Application\DeskPRO\Entity\Conversation
	 * @orm:ManyToOne(targetEntity="ChatConversation", fetch="EAGER")
	 * @orm:JoinColumn(name="conversation_id", referencedColumnName="id")
	 */
	protected $conversation = null;

	/**
	 * Person who created the message
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="author_id", referencedColumnName="id")
	 */
	protected $author = null;

	/**
	 * The message
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}