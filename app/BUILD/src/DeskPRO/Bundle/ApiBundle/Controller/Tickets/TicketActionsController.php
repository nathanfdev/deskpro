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
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{ticket}/actions")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
 */
class TicketActionsController extends BaseController
{
    use TicketSaveTrait;

    /**
     * Lock a ticket.
     *
     * @ApiDoc(
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource update",
     *          400="We will return this in case your request was malformed"
     *      }
     * )
     *
     * @Rest\PUT("/lock")
     *
     * @param Request $request
     * @param Ticket  $ticket
     *
     * @return View
     */
    public function lockAction(Request $request, Ticket $ticket)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, new PermissionGroupContext($ticket));

        if (!$ticket->hasLock() || $this->isForce($request)) {
            $ticket->disableAutoTicketProcess();
            $ticket->setLockedByAgent($this->getUser());
        } else {
            throw $this->createBadRequestException('Ticket is already locked');
        }

        $this->saveTicket($ticket);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Unlock a ticket.
     *
     * @ApiDoc(
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource update",
     *          400="We will return this in case your request was malformed"
     *      }
     * )
     *
     * @Rest\PUT("/unlock")
     *
     * @param Request $request
     * @param Ticket  $ticket
     *
     * @return View
     */
    public function unlockAction(Request $request, Ticket $ticket)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, new PermissionGroupContext($ticket));

        if (!$ticket->hasLock()) {
            throw $this->createBadRequestException('Ticket is not locked');
        }
        if ($ticket->getLockedByAgent() === $this->getUser() || $this->isForce($request)) {
            $ticket->disableAutoTicketProcess();
            $ticket->unlockTicket();
        } else {
            throw $this->createBadRequestException('Ticket is locked by another agent');
        }

        $this->saveTicket($ticket);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isForce(Request $request)
    {
        return $request->request->getBoolean('force');
    }
}
