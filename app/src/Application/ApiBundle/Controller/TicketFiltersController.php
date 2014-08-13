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
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;

class TicketFiltersController extends AbstractController implements ProtectedControllerInterface
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

	public function listAction()
	{
		$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getDefinedFilters();

		$data = array();

		foreach ($filters as $filter) {
			$row = array(
				'id'                => $filter->id,
				'title'             => $filter->title,
				'is_enabled'        => $filter->is_enabled,
				'sys_name'          => $filter->sys_name,
				'display_order'     => $filter->display_order,
				'is_global'         => $filter->is_global,
				'person'            => $filter->person ? $filter->person->toApiData(true) : null,
				'agent_team'        => $filter->agent_team ? $filter->agent_team->toApiData(true) : null,
			);

			$data[] = $row;
		}

		return $this->createApiResponse(array(
			'filters' => $data
		));
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$filter = $this->em->getRepository('DeskPRO:TicketFilter')->find($id);

		if (!$filter || $filter->sys_name) {
			throw $this->createNotFoundException();
		}

		$trans = new LegacyTermsTransformer();
		$crit = $trans->toFilterTerms($filter->terms);

		$filter = $this->getApiData($filter);
		$filter['terms'] = $crit->exportToArray();

		return $this->createApiResponse(array(
			'filter' => $filter
		));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {
			$filter = $this->em->getRepository('DeskPRO:TicketFilter')->find($id);

			if (!$filter || $filter->sys_name) {
				throw $this->createNotFoundException();
			}
		} else {
			$filter = new TicketFilter();
			$filter->person = $this->person;
		}

		$filter->title = $this->in->getString('filter.title');
		$filter->is_global = $this->in->getBool('filter.is_global');

		$filter->person = null;
		if ($this->in->getUint('filter.person_id')) {
			$filter->person = $this->container->getAgentData()->get($this->in->getUint('filter.person_id'));
		}

		$filter->agent_team = null;
		if ($this->in->getUint('filter.agent_team_id')){
			$filter->agent_team = $this->container->getAgentData()->getTeam($this->in->getUint('filter.agent_team_id'));
		}

		$crit = new FilterTerms();
		foreach ($this->in->getArrayValue('filter.terms') as $term_info) {
			try {
				$crit->addTermFromArray($term_info);
			} catch (\Exception $e) {
				// Ignore invalid terms
			}
		}

		$trans = new LegacyTermsTransformer();
		$filter->terms = $trans->toLegacyTerms($crit);

		$this->em->persist($filter);
		$this->em->flush();

		if ($id) {
			return $this->createSuccessResponse();
		} else {
			return $this->createApiCreateResponse(
				array('filter_id' => $filter->id),
				$this->generateUrl('api_ticket_filters_get', array('id' => $filter->id))
			);
		}
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		$filter = $this->em->getRepository('DeskPRO:TicketFilter')->find($id);

		if (!$filter || $filter->sys_name) {
			throw $this->createNotFoundException();
		}

		$this->em->remove($filter);
		$this->em->flush();

		return $this->createSuccessResponse(array(
			'old_id' => $id
		));
	}

	####################################################################################################################
	# save-display-order
	####################################################################################################################

	public function saveDisplayOrderAction()
	{
		$display_order = $this->in->getCleanValueArray('display_order', 'uint', 'discard');
		$this->em->getRepository('DeskPRO:TicketFilter')->updateDisplayOrder($display_order);

		return $this->createSuccessResponse();
	}
}