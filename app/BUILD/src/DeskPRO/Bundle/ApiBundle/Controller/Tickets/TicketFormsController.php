<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFormsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_forms/{context}", requirements={"context"="(agent|user)"})
 */
class TicketFormsController extends AbstractTicketsController
{
    public static $exposeOnly         = [];
    public static $type               = TicketWithLayoutsApiType::class;
    public static $forcePartialUpdate = true;

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Create a new resource",
     *     requirements={
     *         {
     *             "name"="context",
     *             "requirement"="agent|user",
     *             "description"="Ticket layout context",
     *             "dataType"="string"
     *         }
     *     },
     *     statusCodes={
     *         200="Everything is OK",
     *         400="Returned if request is malformed",
     *         403="You are not allowed to edit this layout"
     *     },
     *     output="Application\DeskPRO\Entity\Ticket"
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postContextAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, new PermissionGroupContext(Ticket::class));

        return $this->handleForm($this->instantiateEntity($request), $request);
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Update an existing resource",
     *     requirements={
     *         {
     *             "name"="context",
     *             "requirement"="agent|user",
     *             "description"="Ticket layout context",
     *             "dataType"="string"
     *         },
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="The id of the resource",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Everything is OK",
     *         400="Returned if request is malformed",
     *         403="You are not allowed to edit this layout"
     *     }
     * )
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function putContextAction($id, Request $request)
    {
        $ticket = $this->findEntity($id, $request);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, new PermissionGroupContext($ticket));

        return $this->handleForm($ticket, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person'              => $this->getUser(),
            'ticket_view_context' => $request->attributes->get('context'),
            'ticket_visibility'   => $model->getId() ? TicketWithLayoutsContext::VISIBILITY_EDIT : TicketWithLayoutsContext::VISIBILITY_NEW,
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
