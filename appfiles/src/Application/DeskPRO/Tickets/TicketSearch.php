<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

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
		$options = array();

		if ($personp['is_agent']) {
			$options['agents'] = App::getOrm()->getRepository('DeskPRO:Person')->getAgentNames();
		}

		$options['products']    = App::getOrm()->getRepository('DeskPRO:Product')->getProductNames();
		$options['departments'] = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentNames();
		$options['categories']  = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getAllCategoryNames();
		$options['priorities']  = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();

		return $options;
	}
}