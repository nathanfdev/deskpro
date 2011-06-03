<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ClientMessage\MessageHandler;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * A new chat message
 */
class ChatMessage extends AbstractMessageHandler
{
	/**
	 * Get the message to give the client.
	 *
	 * @param  $context
	 * @return mixed
	 */
	function getMessage($context)
	{
		return $this->message['data'];
	}
}