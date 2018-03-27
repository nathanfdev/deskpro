<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

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
 * @Rest\Route("/ticket_labels")
 */
class TicketLabelsController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get tickets with the given label",
     *     requirements={
     *         {"name"="label", "requirement"="\w", "dataType"="string", "description"="label to filter tickets"},
     *     },
     *     statusCodes={
     *         200="Will be returned in case of success",
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     *
     * @param Request $request
     * @param string  $label
     *
     * @return View
     *
     * @Rest\Get("/{label}/tickets")
     */
    public function getTicketsAction(Request $request, $label)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, ['labels' => [$label]]);
    }
}
