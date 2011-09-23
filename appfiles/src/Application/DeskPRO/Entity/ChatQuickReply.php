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

use Orb\Util\Arrays;

use Application\DeskPRO\App;

/**
 * Ticket macros
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ChatQuickReply")
 * @ORM_Mapping\Table(name="chat_quick_replies")
 */
class ChatQuickReply extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * Who created/owns this quick reply
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * Gloabl means everyone can see/use it. But only owner can edit it.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
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
