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
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;

class TicketLayoutsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


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


	####################################################################################################################
	# get-field-status
	####################################################################################################################

	public function getFieldStatusAction($field_id)
	{
		$dm = $this->getContainer()->getTicketDepartments();
		$lm = $this->getContainer()->getTicketLayoutManager();

		$agent_status = array();
		$user_status  = array();

		foreach ($lm->getAgentLayouts() as $id => $layout) {
			$dep = null;
			if ($id) {
				$dep = $dm->getById($id);
				if (!$dep) continue;
				$dep = $dep->toApiData();
			}
			$agent_status[$id] = array(
				'department' => $dep,
				'enabled' => $layout->has($field_id)
			);
		}
		foreach ($lm->getUserLayouts() as $id => $layout) {
			$dep = null;
			if ($id) {
				$dep = $dm->getById($id);
				if (!$dep) continue;
				$dep = $dep->toApiData();
			}
			$user_status[$id] = array(
				'department' => $dep,
				'enabled' => $layout->has($field_id)
			);
		}

		$sort_fn = function($a, $b) {
			$ao = $a['department'] ? $a['department']['display_order'] : -1000;
			$bo = $b['department'] ? $b['department']['display_order'] : -1000;

			if ($ao == $bo) return 0;
			return $ao < $bo ? -1 : 1;
		};
		uasort($agent_status, $sort_fn);
		uasort($user_status, $sort_fn);

		return $this->createApiResponse(array(
			'agent_layouts' => $agent_status,
			'user_layouts'  => $user_status
		));
	}

	####################################################################################################################
	# save-field-status
	####################################################################################################################

	public function saveFieldStatusAction($field_id)
	{
		$enable_user_layouts  = $this->in->getArrayOfUInts('enable_user_layouts');
		$enable_agent_layouts = $this->in->getArrayOfUInts('enable_agent_layouts');

		$layout_records = $this->em->getRepository('DeskPRO:TicketLayout')->findAll();

		$field_type = $field_id;
		$field_type_id = null;

		if (preg_match('#^(ticket_field)_(\d+)$#', $field_id, $m)) {
			$field_type = $m[1];
			$field_type_id = $m[2];
		}

		foreach ($layout_records as $layout) {
			/** @var $layout TicketLayout */
			$dep_id = $layout->department ? $layout->department->id : 0;

			$user_layout  = clone $layout->user_layout;
			$agent_layout = clone $layout->agent_layout;

			$has_user  = $layout->user_layout->has($field_id);
			$has_agent = $layout->agent_layout->has($field_id);

			$want_user  = in_array($dep_id, $enable_user_layouts);
			$want_agent = in_array($dep_id, $enable_agent_layouts);

			$change = false;

			if ($has_user && !$want_user) {
				$user_layout->remove($field_id);
				$change = true;
			} else if (!$has_user && $want_user) {
				$field = new LayoutField($field_type, $field_type_id);
				$field->setOptionsFromArray(array(
					'on_editticket' => true,
					'on_viewticket' => true,
					'on_newticket' => true
				));
				$user_layout->add($field);
				$change = true;
			}

			if ($has_agent && !$want_agent) {
				$agent_layout->remove($field_id);
				$change = true;
			} else if (!$has_agent && $want_agent) {
				$field = new LayoutField($field_type, $field_type_id);
				$field->setOptionsFromArray(array(
					'on_editticket' => true,
					'on_viewticket' => true,
					'on_newticket' => true
				));
				$agent_layout->add($field);
				$change = true;
			}

			if ($change) {
				$layout->user_layout  = $user_layout;
				$layout->agent_layout = $agent_layout;
				$layout->date_updated = new \DateTime();
				$this->em->persist($layout);
			}
		}

		$this->em->flush();

		return $this->createApiSuccessResponse();
	}
}