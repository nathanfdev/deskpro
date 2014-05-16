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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;

class TicketTriggersController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction($type = null)
	{
		if (!$type || $type == 'all') {
			$type = null;
		}

		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getTriggers($type);

		$data = $this->getApiData($triggers);

		return $this->createApiResponse(array(
			'triggers' => $data
		));
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger) {
			return $this->createNotFoundException();
		}

		$data = $this->getApiData($trigger);

		return $this->createApiResponse(array(
			'trigger' => $data
		));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {
			$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
			if (!$trigger) {
				return $this->createNotFoundException();
			}
		} else {
			$trigger = new TicketTrigger();
		}

		$trigger->title         = $this->in->getString('title');
		$trigger->event_trigger = $this->in->getString('event_trigger');

		if ($trigger->event_trigger == TicketTrigger::EVENT_TYPE_UPDATE) {
			if ($this->in->getBool('flags.run_newreply')) {
				$trigger->addEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY);
			} else {
				$trigger->removeEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY);
			}
		}

		$trigger->setByAgentMode($this->in->getArrayOfStrings('by_agent_mode'));
		$trigger->setByUserMode($this->in->getArrayOfStrings('by_user_mode'));

		$terms = new TriggerTerms();
		foreach ($this->in->getArrayValue('criteria_sets') as $set) {
			if ($set) {
				$terms->addTermFromArray(array('set_terms' => $set));
			}
		}

		$action_defs = $this->container->getTicketActionDefManager();

		$actions = new TriggerActions();
		foreach ($this->in->getArrayValue('actions') as $act) {
			if ($act) {
				$type = $act['type'];

				if ($action_defs->hasNamedDef($type)) {
					$act['type_class'] = $action_defs->getNamedDef($type)->getDef()->getTriggerActionClass();
					if (!$act['type_class']) {
						continue;
					}
				}

				$actions->addActionFromArray($act);
			}
		}

		$trigger->terms = $terms;
		$trigger->actions = $actions;

		$this->em->persist($trigger);
		$this->em->flush();

		return $this->createSuccessResponse(array(
			'trigger_id' => $trigger->id
		));
	}

	####################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($id)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger) {
			return $this->createNotFoundException();
		}

		$old_id = $trigger->id;

		$this->em->remove($trigger);
		$this->em->flush();

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}

	####################################################################################################################
	# toggle-trigger
	####################################################################################################################

	public function toggleTriggerAction($id, $is_enabled)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger) {
			throw $this->createNotFoundException();
		}

		$trigger->is_enabled = $is_enabled;
		$this->em->persist($trigger);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# save-run-order
	####################################################################################################################

	public function saveRunOrderAction()
	{
		$run_orders = $this->in->getCleanValueArray('run_orders', 'uint', 'discard');
		$this->em->getRepository('DeskPRO:TicketTrigger')->updateRunOrders($run_orders);

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get-custom-actions
	####################################################################################################################

	public function getCustomActionsAction()
	{
		$manager = $this->container->getTicketActionDefManager();

		$actions = array();
		foreach ($manager->getAllDefs() as $d) {
			$actions[] = $d->toApiData();
		}

		return $this->createJsonResponse(array('action_defs' => $actions));
	}
}