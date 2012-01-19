<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFilter;
use Symfony\Component\DependencyInjection\ContainerAware;

class TicketSearch
{
	/**
	 * Get an array of various available options for search criteria.
	 *
	 * @param mixed $person The person we're fetching for. This will determine the permissions.
	 * @return array
	 */
	public function getSearchOptions($person)
	{
		App::getApi('tickets')->getTicketOptions($person);
	}
}
