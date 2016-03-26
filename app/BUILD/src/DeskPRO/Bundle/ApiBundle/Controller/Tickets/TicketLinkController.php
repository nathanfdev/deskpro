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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketLinkController.
 *
 * @ApiModes("all")
 */
class TicketLinkController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="link two tickets",
     *     requirements={
     *         {"name"="ticketId", "requirement"="\d+", "dataType"="integer", "description"="base ticket"},
     *         {"name"="link_ticket_id", "requirement"="\d+", "dataType"="integer", "description"="ticket to link"},
     *         {
     *             "name"="parent",
     *             "requirement"="true|false",
     *             "dataType"="boolean",
     *             "description"="set true if you want to make link ticket as parent for ticket"},
     *     },
     *     statusCodes={
     *         201="Tickets was linked successfully",
     *         400="You are trying to link ticket to itself",
     *         404={
     *             "Ticket with 'ticketId' wasn't found",
     *             "Ticket with 'link_ticket_id' wasn't found",
     *         },
     *     },
     * )
     *
     * @param Request $request
     * @param int     $ticketId
     
     * @return View
     *
     * @Annotations\Post("/tickets/{ticketId}/link", name="api_tickets_link")
     */
    public function postAction(Request $request, $ticketId)
    {
        $linkTicketId = $request->request->get('link_ticket_id');

        $ticket     = $this->get('ticket_manager')->getTicket($ticketId);
        $linkTicket = $this->get('ticket_manager')->getTicket($linkTicketId);

        if ((int) $ticketId === (int) $linkTicketId) {
            throw new BadRequestHttpException('You can\'t link ticket to itself!');
        }

        if (!$ticket) {
            throw new NotFoundHttpException(sprintf('Ticket with id [ %d ] not found', $ticketId));
        }
        if (!$linkTicket) {
            throw new NotFoundHttpException(sprintf('Ticket with id [ %d ] not found', $linkTicketId));
        }

        $make_parent = $request->request->get('parent', false);
        $linker      = $this->get('tickets.linker');

        $make_parent ? $linker->linkTickets($ticket, $linkTicket) : $linker->linkTickets($linkTicket, $ticket);

        return View::create(
            [],
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_tickets_link_list', array('ticketId' => $ticketId)),
            ]
        );
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="get tickets linked with ticket under provided id",
     *     requirements={
     *         {"name"="ticketId", "requirement"="\d+", "dataType"="integer", "description"="ticket to find id"},
     *     },
     *     statusCodes={
     *         200="Returned in case of successful request",
     *         404="Ticket with specified id wasn't found",
     *     },
     * )
     *
     * @param int $ticketId
     *
     * @Annotations\Get("/tickets/{ticketId}/link", name="api_tickets_link_list")
     *
     * @return View
     */
    public function listAction($ticketId)
    {
        $ticket = $this->get('ticket_manager')->getTicket($ticketId);

        if (!$ticket) {
            throw new NotFoundHttpException(sprintf('Ticket with id [ %d ] not found', $ticketId));
        }

        $linker = $this->get('tickets.linker');

        return View::create(
            $this->wrap($linker->getLinkedTickets($ticket)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="delete relation between two tickets",
     *     requirements={
     *         {"name"="ticketId", "requirement"="\d+", "dataType"="integer", "description"="base ticket"},
     *         {"name"="unlinkTicketId", "requirement"="\d+", "dataType"="integer", "description"="ticket to unlink"},
     *         {
     *             "name"="link_type",
     *             "requirement"="parent|sibling|child",
     *             "dataType"="string",
     *             "description"="you have to specify relation type to unlink tickets properly"},
     *     },
     *     statusCodes={
     *         201="Tickets was unlinked successfully",
     *         400={
     *             "You are trying to unlink ticket from itself",
     *             "Wrong relation type",
     *         },
     *         404={
     *             "Ticket with 'ticketId' wasn't found",
     *             "Ticket with 'unlinkTicketId' wasn't found",
     *         },
     *     },
     * )
     *
     * @param int     $ticketId
     * @param int     $unlinkTicketId
     * @param Request $request
     *
     * @Annotations\Delete("/tickets/{ticketId}/link/{unlinkTicketId}", name="api_tickets_link_unlink")
     *
     * @return View
     */
    public function deleteAction(Request $request, $ticketId, $unlinkTicketId)
    {
        $linkType = $request->query->get('link_type');
        $linker   = $this->get('tickets.linker');

        if ((int) $ticketId === (int) $unlinkTicketId) {
            throw new BadRequestHttpException('You can\'t unlink ticket from itself!');
        }

        try {
            $linker->unlinkTickets($ticketId, $unlinkTicketId, $linkType);
        } catch (\InvalidArgumentException $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundHttpException($e->getMessage());
            } else {
                throw new BadRequestHttpException($e->getMessage());
            }
        }

        return View::create(
            [],
            Response::HTTP_OK,
            [
                'Location' => $this->generateUrl('api_tickets_link_list', array('ticketId' => $ticketId)),
            ]
        );
    }
}
