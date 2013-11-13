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

class TicketSlasController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$slas = $this->em->getRepository('DeskPRO:Sla')->getAllSlas();

		$data = array();

		foreach ($slas as $sla) {
			$row = array(
				'id'                => $sla->id,
				'title'             => $sla->title,
				'sla_type'          => $sla->sla_type,
			);

			$data[] = $row;
		}

		return $this->createApiResponse(array(
			'slas' => $data
		));
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$sla = $this->em->find('DeskPRO:Sla', $id);
		if (!$sla) {
			return $this->createNotFoundException();
		}

		$data = $this->getApiData($sla);

		return $this->createApiResponse(array(
			'sla' => $data
		));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {
			$sla = $this->em->find('DeskPRO:Sla', $id);
			if (!$sla) {
				return $this->createNotFoundException();
			}
		} else {
			$sla = new Sla();
		}

		$sla->title         = $this->in->getString('title');
		$sla->event_trigger = $this->in->getString('event_trigger');

		$sla->setByAgentMode($this->in->getArrayOfStrings('by_agent_mode'));
		$sla->setByUserMode($this->in->getArrayOfStrings('by_user_mode'));

		$terms = new TriggerTerms();
		foreach ($this->in->getArrayValue('criteria_sets') as $set) {
			if ($set) {
				$terms->addTermFromArray(array('set_terms' => $set));
			}
		}

		$actions = new TriggerActions();
		foreach ($this->in->getArrayValue('actions') as $act) {
			if ($act) {
				$actions->addActionFromArray($act);
			}
		}

		$sla->terms = $terms;
		$sla->actions = $actions;

		$this->em->persist($sla);
		$this->em->flush();

		return $this->createSuccessResponse(array(
			'trigger_id' => $sla->id
		));
	}

	####################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($id)
	{
		$sla = $this->em->find('DeskPRO:Sla', $id);
		if (!$sla) {
			return $this->createNotFoundException();
		}

		$old_id = $sla->id;

		$this->em->remove($sla);
		$this->em->flush();

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}
}