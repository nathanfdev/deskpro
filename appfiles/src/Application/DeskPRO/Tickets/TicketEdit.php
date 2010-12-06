<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

class TicketEdit
{
	/**
	 * Get an array of various options used on the new ticket page.
	 *
	 * @param mixed $person The person we're fetching for. This will define the permissions/context.
	 * @return array
	 */
	public function getNewTicketOptions($person)
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