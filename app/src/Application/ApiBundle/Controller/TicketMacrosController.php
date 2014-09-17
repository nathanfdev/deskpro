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
use Application\DeskPRO\Entity\TicketMacro;

class TicketMacrosController extends AbstractController implements ProtectedControllerInterface
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
		$macros = $this->em->getRepository('DeskPRO:TicketMacro')->getMacros();

		$data = array();

		foreach ($macros as $macro) {
			$row = array(
				'id'                => $macro->id,
				'title'             => $macro->title,
				'is_enabled'        => $macro->is_enabled,
				'is_global'         => $macro->is_global,
				'person_id'         => $macro->person ? $macro->person->id : null,
				'person'            => $macro->person ? $macro->person->toApiData(true) : null,
			);

			$data[] = $row;
		}

		return $this->createApiResponse(array(
			'macros' => $data
		));
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$macro = $this->em->find('DeskPRO:TicketMacro', $id);
		if (!$macro) {
			throw $this->createNotFoundException();
		}

		$data = $this->getApiData($macro);

		return $this->createApiResponse(array(
			'macro' => $data
		));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {
			$macro = $this->em->find('DeskPRO:TicketMacro', $id);
			if (!$macro) {
				throw $this->createNotFoundException();
			}
		} else {
			$macro = new TicketMacro();
		}

		$macro->title = $this->in->getString('title');

		if ($this->in->getBool('is_global')) {
			$macro->is_global = true;
		} else {
			$macro->is_global = false;
			$macro->person = $this->container->getAgentData()->get($this->in->getUint('person_id'));
		}

		if (!$macro->person) {
			$macro->is_global = true;
		}

		//TODO
		//$actions = new MacroActions();
		//$actions->importFromArray(array('actions' => $this->in->getArrayValue('actions')));
		//$macro->actions = $actions;

		$this->em->persist($macro);
		$this->em->flush();

		return $this->createSuccessResponse(array(
			'macro_id' => $macro->id
		));
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		$macro = $this->em->find('DeskPRO:TicketMacro', $id);
		if (!$macro) {
			throw $this->createNotFoundException();
		}

		$old_id = $macro->id;

		$this->em->remove($macro);
		$this->em->flush();

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}
}