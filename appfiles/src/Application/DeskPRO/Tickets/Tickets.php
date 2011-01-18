<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
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
			->getRepository('DeskPRO:Ticket')
			->getTicketsFromIds($ids);
	}


	
	/**
	 * @param Ticket $ticket
	 * @return TicketEdit
	 */
	public function getTicketEditor(Entity\Ticket $ticket)
	{
		$ticket_edit = new TicketEdit($ticket);
		return $ticket_edit;
	}


	
	/**
	 * Get an array of various options used on the new ticket page.
	 *
	 * @param mixed $person The person we're fetching for. This will define the permissions/context.
	 * @return array
	 */
	public function getTicketOptions($person)
	{
		$options = array();

		if ($person['is_agent']) {
			$options['agents'] = App::getOrm()->getRepository('DeskPRO:Person')->getAgentNames();
		}

		$options['products']    = App::getOrm()->getRepository('DeskPRO:Product')->getProductNames();
		$options['departments'] = App::getOrm()->getRepository('DeskPRO:Department')->getFlatDepartmentNames(null, false);
		$options['categories']  = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getAllCategoryNames();
		$options['priorities']  = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();

		return $options;
	}
}