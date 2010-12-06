<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\TicketQueue;
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
			$options['agents'] = App::getOrm()->getRepository('CoreBundle:Person')->getAgentNames();
		}

		$options['products']    = App::getOrm()->getRepository('CoreBundle:Product')->getProductNames();
		$options['departments'] = App::getOrm()->getRepository('CoreBundle:Department')->getDepartmentNames();
		$options['categories']  = App::getOrm()->getRepository('CoreBundle:TicketCategory')->getAllCategoryNames();
		$options['priorities']  = App::getOrm()->getRepository('CoreBundle:TicketPriority')->getPriorityNames();

		return $options;
	}
}