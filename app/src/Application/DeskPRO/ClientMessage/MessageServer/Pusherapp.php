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

use Symfony\Component\EventDispatcher\Event;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;

/**
 * A message server is something that listenes on the ClientMessages event
 * to handle dispatching messages through various protocols.
 */
class Pusherapp extends AbstractMessageServer
{
	protected $pusher;

	protected function init(array $options)
	{
		$this->pusher = new \Pusher(
			$options['api_key'],
			$options['secret'],
			$options['app_id']
		);
	}



	/**
	 * Handle a new message
	 *
	 * @param ClientMessage $message
	 * @return mixed
	 */
	function handleNewMessage(Entity\ClientMessage $message)
	{
		// We use dots, pusherapp doesnt allow them so lets use dahs
		$full_name = str_replace('.', '-', $message['channel']);

		list ($channel, $event_name) = Strings::rexplode('-', $full_name, 2);

		$this->pusher->trigger($channel, $event_name, $message['data']);
	}
}
