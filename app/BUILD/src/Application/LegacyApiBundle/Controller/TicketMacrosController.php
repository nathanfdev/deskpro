<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TicketMacro;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketMacroType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Simple ticket macros CRUD.
 *
 * SWG\Resource(
 * 	resourcePath="/ticket_macros",
 * 	description="Operations about Ticket macros",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class TicketMacrosController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    /**
     * @return JsonResponse;
     *
     * SWG\Api(
     * 	path="/ticket_macros",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of ticket macroses",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
    public function listAction()
    {
        $macros = $this->em->getRepository(TicketMacro::class)->getMacros();
        $data   = [];

        /** @var TicketMacro $macro */
        foreach ($macros as $macro) {
            $row = [
                'id'         => $macro->getId(),
                'title'      => $macro->getTitle(),
                'is_enabled' => $macro->isEnabled(),
                'is_global'  => $macro->getIsGlobal(),
                'person_id'  => $macro->getPerson() ? $macro->getPerson()->getId() : null,
                'person'     => $macro->getPerson() ? $macro->getPerson()->toApiData(true) : null,
                'department' => $macro->getDepartment() ? $macro->getDepartment()->toApiData(true) : null,
            ];

            $data[] = $row;
        }

        return $this->createApiResponse([
            'macros' => $data,
        ]);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $macro = $this->em->find('DeskPRO:TicketMacro', $id);
        if (!$macro) {
            throw $this->createNotFoundException();
        }

        $data = $this->getApiData($macro);

        return $this->createApiResponse([
            'macro' => $data,
        ]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    /**
     * @param TicketMacro $macro
     * @param Request     $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
     *
     *
     *
     * SWG\Api(
     * 	path="/ticket_macros",
     * 	SWG\Operation(
     * 		method="PUT",
     * 		summary="Create new macros",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="title",
     *				description="Macros name",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="is_global",
     *				description="Mark/unmark macros as global",
     *				paramType="query",
     *				required=false,
     *				type="boolean",
     *			),
     *          SWG\Parameter(
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
    public function saveAction(TicketMacro $macro, Request $request)
    {
        $form = $this->createForm(TicketMacroType::class, $macro);
        // Set second argument to false because
        // we don't want to set Macro fields to NULL when they are missing in the submitted data
        // for example we don't pass Macro `actions` to this endpoint when we just edit macro permissions
        $form->submit($request->request->all(), false);

        $this->em->persist($macro);
        $this->em->flush();

        return $this->createSuccessResponse([
            'macro_id' => $macro->getId(),
        ]);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
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

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }
}
