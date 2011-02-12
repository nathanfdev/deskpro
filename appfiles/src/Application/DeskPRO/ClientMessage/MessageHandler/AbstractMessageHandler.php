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
 * A message handler decides how to take a message and compose a suitable
 * data packet for the client. For example, it might just be an ID so the
 * client can callback for full data, or it might be all the data now etc.
 */
abstract class AbstractMessageHandler
{
	/**
	 * @var Application\DeskPRO\Entity\ClientMessage
	 */
	protected $message;

	public function __construct(Entity\ClientMessage $message)
	{
		$this->message;
	}

	/**
	 * Get the message to give the client.
	 *
	 * @param  $context
	 * @return mixed
	 */
	abstract function getMessage($context);
}