<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

class TicketFieldsController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$data = array();

		/** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
		$field_manager = $this->container->getSystemService('ticket_fields_manager');

		$custom_fields = $field_manager->getDefinedFields();
		$data['custom_fields']  = $this->getApiData($custom_fields, false);

		$data['product_enabled']  = $field_manager->isProductEnabled();
		$data['category_enabled'] = $field_manager->isCategoryEnabled();
		$data['priority_enabled'] = $field_manager->isPriorityEnabled();
		$data['workflow_enabled'] = $field_manager->isWorkflowEnabled();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# toggleField
	####################################################################################################################

	public function toggleFieldAction($field_id, $is_enabled)
	{
		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$field_manager->setFieldEnabledById($field_id, $is_enabled);

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# list-categories
	####################################################################################################################

	public function listCategoriesAction()
	{
		$data = array();

		$ticket_cats = $this->container->getSystemService('ticket_categories');
		$flat_array = $ticket_cats->getFlatArray();

		$cats = array();
		foreach ($flat_array as $row) {
			$cats[] = $row['object'];
		}

		$data['categories']     = $this->getApiData($cats, false);
		$data['default_id']     = $ticket_cats->count() ? $ticket_cats->getDefaultCategory()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_cat_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_cat_agent_required') ? true : false;

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# list-workflows
	####################################################################################################################

	public function listWorkflowsAction()
	{
		$data = array();

		$ticket_works = $this->container->getSystemService('ticket_workflows');

		$data['categories']     = $this->getApiData($ticket_works->getAll(), false);
		$data['default_id']     = $ticket_works->count() ? $ticket_works->getDefaultWorkflow()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_work_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_work_agent_required') ? true : false;

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# list-priorities
	####################################################################################################################

	public function listPrioritiesAction()
	{
		$data = array();

		$ticket_pris = $this->container->getSystemService('ticket_priorities');

		$data['categories']     = $this->getApiData($ticket_pris->getAll(), false);
		$data['default_id']     = $ticket_pris->count() ? $ticket_pris->getDefaultPriority()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_pri_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_pri_agent_required') ? true : false;

		return $this->createApiResponse($data);
	}
}