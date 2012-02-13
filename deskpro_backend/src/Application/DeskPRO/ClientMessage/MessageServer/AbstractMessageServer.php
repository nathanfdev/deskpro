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

use Application\DeskPRO\ClientMessage\Event;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;


/**
 * A message server is something that listenes on the ClientMessages event
 * to handle dispatching messages through various protocols.
 */
abstract class AbstractMessageServer
{
	public function __construct(array $options)
	{
		$event_dispatcher = App::get('event_dispatcher');
		$event_dispatcher->addListener('DeskPRO_onNewClientMessage', $this);

		$this->init($options);
	}

	protected function init(array $options)
	{
		// hook for children
	}



	public function DeskPRO_onNewClientMessage(Event $event)
	{
		$this->handleNewMessage($event->getClientMessage());
	}



	/**
	 * Handle a new message
	 *
	 * @param ClientMessage $message
	 * @return mixed
	 */
	abstract function handleNewMessage(Entity\ClientMessage $message);
}
