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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to ticket slas.
 *
 * @ApiModes("all")
 */
class TicketSlasController extends CrudSubController
{
    public static $parentProperty = 'ticket';
    public static $entity         = TicketSla::class;

    /**
     * Retrieve the list of ticket's SLAs.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     resourceDescription="Operations about ticket SLAs",
     *     tags={"CRUD"="#ffa500"},
     *     description="get SLAs collection for single ticket",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="array<Application\DeskPRO\Entity\TicketSla>"
     * )
     *
     * @Rest\Get("/tickets/{parentId}/slas", name="api_ticket_sla")
     *
     * @param Request $request
     *
     * @return View
     *
     * @internal param int $ticket_id
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * Retrieve single SLA for ticket by parent SLA's ID.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     resourceDescription="Operations about ticket SLAs",
     *     tags={"CRUD"="#ffa500"},
     *     description="single SLA for ticket by parent SLA's ID",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *         404="Ticket SLA not found"
     *     },
     *     output="Application\DeskPRO\Entity\TicketSla"
     * )
     *
     * @Rest\Get("/tickets/{parentId}/slas/{sla_id}", name="api_ticket_sla_single")
     *
     * @param Request $request
     * @param int     $parentId parent Ticket's ID
     * @param int     $sla_id   parent SLA's ID
     *
     * @return View
     */
    public function getSingleSlaAction(Request $request, $parentId, $sla_id)
    {
        $ticketSla = $this->getRepository(TicketSla::class)->findOneBy(['ticket' => $parentId, 'sla' => $sla_id]);
        if (null === $ticketSla) {
            throw $this->createNotFoundException(
                'Ticket SLA for ticket ID= '.$parentId.' and SLA ID='.$sla_id.' not found'
            );
        }

        return parent::getAction($request, $ticketSla->getId());
    }

    /**
     * Delete single Ticket SLA.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     resourceDescription="Operations about ticket SLAs",
     *     tags={"CRUD"="#ffa500"},
     *     description="Delete single Ticket SLA",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *         404="Ticket SLA not found"
     *     },
     *     output="Application\DeskPRO\Entity\TicketSla"
     * )
     *
     * @Rest\Delete("/tickets/{parentId}/slas/{sla_id}", name="api_ticket_sla_single_delete")
     *
     * @param Request $request
     * @param int     $parentId parent Ticket's ID
     * @param int     $sla_id   parent SLA's ID
     *
     * @return View
     */
    public function deleteSingleSlaAction(Request $request, $parentId, $sla_id)
    {
        $ticketSla = $this->getRepository(TicketSla::class)->findOneBy(['ticket' => $parentId, 'sla' => $sla_id]);
        if (null === $ticketSla) {
            throw $this->createNotFoundException(
                'Ticket SLA for ticket ID= '.$parentId.' and SLA ID='.$sla_id.' not found'
            );
        }

        return parent::deleteAction($ticketSla->getId(), $request);
    }
}
