<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to ticket labels.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_statuses")
 */
class TicketStatusesController extends BaseController
{
    /**
     * Fetch available ticket statuses.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get all available statuses for tickets, sorted alphabetically",
     *     statusCodes={
     *         200="Will be returned if everything is ok"
     *     },
     *     output="array<string>"
     * )
     *
     * @Rest\Get("")
     */
    public function listAction()
    {
        return View::create($this->wrap(Ticket::getTicketStatuses()));
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get tickets with the given status",
     *     requirements={
     *         {"name" = "status", "requirement"="\w", "description" = "provide a status to filter", "dataType" = "string"},
     *     },
     *     statusCodes={
     *         200="Returned if request was successful"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     * @Rest\Get("/{status}/tickets")
     *
     * @param Request $request
     * @param string  $status
     *
     * @return View
     */
    public function getTicketsForStatusAction(Request $request, $status)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, ['status' => $status]);
    }
}
