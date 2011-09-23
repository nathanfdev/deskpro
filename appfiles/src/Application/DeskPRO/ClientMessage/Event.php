<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ClientMessage
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ClientMessage;

use Application\DeskPRO\Entity\ClientMessage;

class Event extends \Symfony\Component\EventDispatcher\Event
{
	protected $client_message;

	public function construct(ClientMessage $client_message)
	{
		$this->client_message = $client_message;
	}


	/**
	 * @return \Application\DeskPRO\Entity\ClientMessage
	 */
	public function getClientMessage()
	{
		return $this->client_message;
	}
}
