<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
