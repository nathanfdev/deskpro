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

use \Orb\Util\Arrays;

use Application\DeskPRO\App;

/**
 * Ticket macros
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ChatQuickReply")
 * @orm:Table(name="chat_quick_replies")
 */
class ChatQuickReply extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * Who created/owns this quick reply
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * Gloabl means everyone can see/use it. But only owner can edit it.
	 * 
	 * @var bool
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content = '';

	/**
	 * @param  $conversation
	 * @return string
	 */
	public function getReplyForConversation($conversation)
	{
		$content = $this->content;
		$content = str_replace(
			array('{person_name}'),
			array($conversation['person_name']),
			$content
		);

		return $content;
	}
}