<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Symfony\Component\DependencyInjection\ContainerAware;

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

			if (App::getSetting('core.use_agent_team')) {
				$options['agent_teams'] = App::getOrm()->getRepository('DeskPRO:AgentTeam')->getTeamNames();
			} else {
				$options['agent_teams'] = array();
			}
		}

		$options['departments_hierarchy'] = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
		$options['departments_full'] = App::getOrm()->getRepository('DeskPRO:Department')->getFullDepartmentNames(null, false);
		$options['departments'] = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentNames(null, false);

		if (App::getSetting('core.use_ticket_category')) {
			$options['ticket_categories_hierarchy'] = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getCategoriesInHierarchy();
			$options['ticket_categories_full'] = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getFullCategoryNames(null, false);
			$options['ticket_categories'] = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getCategoryNames(null, false);
		} else {
			$options['ticket_categories_hierarchy'] = array();
			$options['ticket_categories_full'] = array();
			$options['ticket_categories'] = array();
		}

		if (App::getSetting('core.use_ticket_workflow')) {
			$options['ticket_workflows'] = App::getOrm()->getRepository('DeskPRO:TicketWorkflow')->getWorkflowNames();
		} else {
			$options['ticket_workflows'] = array();
		}

		if (App::getSetting('core.use_product')) {
			$options['products'] = App::getOrm()->getRepository('DeskPRO:Product')->getProductNames();
			//$options['products_full']       = App::getOrm()->getRepository('DeskPRO:Product')->getFullProductNames(null, false);
			$options['products_hierarchy']  = App::getOrm()->getRepository('DeskPRO:Product')->getProductsInHierarchy();
		} else {
			$options['products'] = array();
			$options['products_hierarchy']  = array();
		}

		if (App::getSetting('core.use_ticket_priority')) {
			$options['priorities']  = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();
		} else {
			$options['priorities'] = array();
		}
		$options['ticket_priorities']  = $options['priorities'];

		return $options;
	}
}
