<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

class Tickets
{
	/**
	 * Get an array of tickets from the passed IDs.
	 * 
	 * @param array $ids
	 * @return array
	 */
	public function getTicketsFromIds(array $ids)
	{
		return App::getOrm()
			->getRepository('CoreBundle:Ticket')
			->getTicketsFromIds($ids);
	}
}