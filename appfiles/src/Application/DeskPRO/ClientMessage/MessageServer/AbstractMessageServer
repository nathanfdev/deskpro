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

namespace Application\DeskPRO\ClientMessage\MessageServer;

use \Symfony\Component\EventDispatcher\Event;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * A message server is something that listenes on the ClientMessages event
 * to handle dispatching messages through various protocols.
 */
abstract class AbstractMessageServer
{
	public function __construct(array $options)
	{
		$event_dispatcher = App::get('event_dispatcher');
		$event_dispatcher->connect('deskpro.client_message.new'. array($this, '_handleNewMessageEvent'));

		$this->init($options);
	}

	protected function init(array $options)
	{
		// hook for children
	}



	protected function _handleNewMessageEvent(Event $event)
	{
		$this->handleNewMessage($event->getSubject());
	}



	/**
	 * Handle a new message
	 *
	 * @param ClientMessage $message
	 * @return mixed
	 */
	abstract function handleNewMessage(Entity\ClientMessage $message);
}