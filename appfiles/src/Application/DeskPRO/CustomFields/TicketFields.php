<?php

namespace Application\DeskPRO\CustomFields;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Symfony\Component\DependencyInjection\ContainerAware;

class TicketFields extends AbstractFields
{
	const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefTicket';
	const ENTITY_NAME  = 'DeskPRO:CustomDefTicket';


	public function userDisplayRuleFilter($ticket_fields)
	{
		$display_elements = App::getOrm()->createQuery("
			SELECT d
			FROM DeskPRO:DepartmentTicketDisplay d
			WHERE d.is_agent_only = ?1
			ORDER BY d.display_order ASC
		")->execute(array(1=>false));
	}
}