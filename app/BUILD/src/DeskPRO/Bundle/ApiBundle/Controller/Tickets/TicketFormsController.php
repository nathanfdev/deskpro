<?php

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
 * @ApiDoc(
 *     target="postContextAction,putContextAction",
 *     input={
 *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiType",
 *         "options"={
 *              "data"="Application\DeskPRO\Entity\Ticket",
 *              "person"="Application\DeskPRO\Entity\Person",
 *              "ticket_view_context"="agent",
 *              "ticket_visibility"="new"
 *         }
 *     }
 * )
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
