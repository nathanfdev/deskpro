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

use Application\DeskPRO\Exception\ValidationException;
use Orb\Util\Arrays;

class TicketTriggersController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getTriggers();

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
		if (!$trigger || $trigger->getTicketTimeField()) {
			return $this->createNotFoundException();
		}

		$data = $this->getApiData($trigger);

		return $this->createApiResponse(array(
			'trigger' => $data
		));
	}

	####################################################################################################################
	# edit
	####################################################################################################################

	public function editAction($id)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger || $trigger->getTicketTimeField()) {
			return $this->createNotFoundException();
		}
	}

	####################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($id)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger || $trigger->getTicketTimeField()) {
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
		if (!$trigger || $trigger->getTicketTimeField()) {
			return $this->createNotFoundException();
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
}