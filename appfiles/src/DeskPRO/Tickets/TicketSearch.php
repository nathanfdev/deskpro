<?php

namespace DeskPRO\Tickets;

use \DeskPRO\App;
use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\TicketQueue;
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
			$options['agents'] = App::getOrm()->getRepository('CoreBundle:Person')->getAgentNames();
		}

		$options['products']    = App::getOrm()->getRepository('CoreBundle:Product')->getProductNames();
		$options['departments'] = App::getOrm()->getRepository('CoreBundle:Department')->getDepartmentNames();
		$options['categories']  = App::getOrm()->getRepository('CoreBundle:TicketCategory')->getAllCategoryNames();
		$options['priorities']  = App::getOrm()->getRepository('CoreBundle:TicketPriority')->getPriorityNames();

		return $options;
	}
}