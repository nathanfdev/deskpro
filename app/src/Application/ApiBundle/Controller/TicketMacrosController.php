<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use \Symfony\Component\HttpFoundation\Response;

/**
 * Simple ticket macros CRUD
 *
 * @SWG\Resource(
 * 	resourcePath="/ticket_macros",
 * 	description="Operations about Ticket macros",
 * 	basePath="/api"
 * )
 */
class TicketMacrosController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}

    /**
     * @return Response;
     *
     * @SWG\Api(
     * 	path="/ticket_triggers",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of ticket macroses",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
	public function listAction()
	{
        /** @var \Application\DeskPRO\EntityRepository\TicketMacro $repo */
        $repo = $this->em->getRepository('DeskPRO:TicketMacro');
        $macros = $repo->getMacros();

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

    /**
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @SWG\Api(
     * 	path="/ticket_layouts/{dep_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Get macros by ID",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Layout ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
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

    /**
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @SWG\Api(
     * 	path="/ticket_macros/{id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Update existing macros by ID",
     * 		notes="If no ID passed then new macros will be created. If person_id is invalid then macro will be marked global.",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Macros ID",
     *				paramType="path",
     *				required=false,
     *				type="integer",
     *			),
     *          @SWG\Parameter(
     *				name="title",
     *				description="Macros name",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          @SWG\Parameter(
     *				name="is_global",
     *				description="Mark/unmark macros as global",
     *				paramType="query",
     *				required=false,
     *				type="boolean",
     *			),
     *          @SWG\Parameter(
     *				name="person_id",
     *				description="Set macros owner",
     *				paramType="query",
     *				required=false,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
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

    /**
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @SWG\Api(
     * 	path="/ticket_macros/{id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete macros by ID",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Macros ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
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