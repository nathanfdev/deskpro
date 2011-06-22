<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketFilter;
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

		if ($person['is_agent']) {
			$options['agents'] = App::getOrm()->getRepository('DeskPRO:Person')->getAgentNames();
			$options['agent_teams'] = App::getOrm()->getRepository('DeskPRO:AgentTeam')->getTeamNames();
		}

		$options['gateway_names'] = App::getOrm()->getRepository('DeskPRO:EmailGateway')->getGatewayNames();

		$options['departments_hierarchy'] = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
		$options['departments_full'] = App::getOrm()->getRepository('DeskPRO:Department')->getFullDepartmentNames(null, false);
		$options['departments'] = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentNames(null, false);

		$options['ticket_categories_hierarchy'] = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getCategoriesInHierarchy();
		$options['ticket_categories_full'] = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getFullCategoryNames(null, false);
		$options['ticket_categories'] = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getCategoryNames(null, false);

		$options['ticket_workflows'] = App::getOrm()->getRepository('DeskPRO:TicketWorkflow')->getWorkflowNames();

		$options['products']    = App::getOrm()->getRepository('DeskPRO:Product')->getProductNames();
		$options['priorities']  = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();

		$options['from_names'] = App::getEntityRepository('DeskPRO:EmailFrom')->getFromNames();

		return $options;
	}
}