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

use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;

class TicketLayoutsController extends AbstractController
{
	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($dep_id = 0)
	{
		$is_default = false;
		$ticket_layout = null;

		if ($dep_id) {
			$dep = $this->container->getSystemService('ticket_departments')->getById($dep_id);
			if (!$dep) {
				throw $this->createNotFoundException();
			}

			$ticket_layout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(array('department' => $dep));
		}

		if (!$ticket_layout) {
			$is_default = true;
			$ticket_layout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(array('department' => null));
		}

		if (!$ticket_layout) {
			$is_default = true;
			$ticket_layout = new TicketLayout(null);
		}

		return $this->createApiResponse(array(
			'layout'     => array(
				'user'  => $ticket_layout->user_layout->exportToArray(),
				'agent' => $ticket_layout->agent_layout->exportToArray(),
			),
			'is_default' => $is_default
		));
	}


	####################################################################################################################
	# stats
	####################################################################################################################

	public function getLayoutStatsAction()
	{
		$deps_with_layouts = $this->db->fetchAllCol("
			SELECT department_id
			FROM ticket_layouts
			WHERE department_id IS NOT NULL
		");
		if ($deps_with_layouts) {
			$deps_with_layouts = array_fill_keys($deps_with_layouts, true);
		}

		$data = array('default' => array(), 'custom' => array());

		/** @var \Application\DeskPRO\Departments\TicketDepartments $ticket_deps */
		$ticket_deps = $this->container->getSystemService('ticket_departments');

		foreach ($ticket_deps->getAll() as $dep) {
			if (isset($deps_with_layouts[$dep->id])) {
				$data['custom'][] = $dep->toApiData();
			} else {
				$data['default'][] = $dep->toApiData();
			}
		}

		return $this->createApiResponse(array(
			'layout_info'   => $data,
			'count_custom'  => count($data['custom']),
			'count_default' => count($data['default']),
		));
	}


	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($dep_id = 0)
	{
		if ($dep_id) {
			$dep = $this->container->getSystemService('ticket_departments')->getById($dep_id);
			if (!$dep) {
				throw $this->createNotFoundException();
			}

			$layout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(array('department' => $dep));
		} else {
			$dep = null;
			$layout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(array('department' => null));
		}

		if (!$layout) {
			$layout = new TicketLayout($dep);
		}

		$user_layout  = new Layout();
		$agent_layout = new Layout();

		foreach ($this->in->getArrayValue('layout.user') as $field_info) {
			$field = new LayoutField($field_info['field_type'], $field_info['field_id'] ?: null);
			$field->setOptionsFromArray($field_info['options']);
			$user_layout->add($field);
		}
		foreach ($this->in->getArrayValue('layout.agent') as $field_info) {
			$field = new LayoutField($field_info['field_type'], $field_info['field_id'] ?: null);
			$field->setOptionsFromArray($field_info['options']);
			$agent_layout->add($field);
		}

		$layout->user_layout  = $user_layout;
		$layout->agent_layout = $agent_layout;

		$this->em->persist($layout);
		$this->em->flush();

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($dep_id)
	{
		$dep = $this->container->getSystemService('ticket_departments')->getById($dep_id);
		if (!$dep) {
			throw $this->createNotFoundException();
		}

		$layout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(array('department' => $dep));
		if ($layout) {
			$this->em->remove($layout);
			$this->em->flush();
		}

		return $this->createSuccessResponse();
	}
}