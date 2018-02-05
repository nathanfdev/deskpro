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

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketSlaType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to ticket slas.
 *
 * @Rest\Route("/tickets/{parentId}/ticket_slas")
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketSla")
 * @ApiDoc(
 *     target="postAction,putAction,postSingleSlaAction,putSingleSlaAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketSlaType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TicketSla",
 *          "ticket"="Application\DeskPRO\Entity\Ticket"
 *      }
 *     }
 * )
 */
class TicketSlasController extends AbstractTicketsCrudSubController
{
    use TicketSaveTrait, TicketAwarePersistModelTrait;

    public static $parentProperty = 'ticket';
    public static $entity         = TicketSla::class;
    public static $type           = TicketSlaType::class;

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket' => $this->findParentOr404(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * Retrieve single SLA for ticket by parent SLA's ID.
     *
     * @ApiDoc(
     *    requirements={
     *        {
     *              "name"="parentId",
     *              "requirement"="\d+",
     *              "description"="the id of parent ticket",
     *              "dataType"="integer"
     *        },
     *        {
     *              "name"="slaId",
     *              "requirement"="\d+",
     *              "description"="the id of parent SLA",
     *              "dataType"="integer"
     *        }
     *     },
     *     output="Application\DeskPRO\Entity\TicketSla"
     * )
     *
     * @Rest\Get("/by_sla/{slaId}")
     *
     * @param Request $request
     * @param int     $parentId parent Ticket's ID
     * @param int     $slaId    parent SLA's ID
     *
     * @return View
     */
    public function getSingleTicketSlaAction(Request $request, $parentId, $slaId)
    {
        $ticketSla = $this->getRepository(TicketSla::class)->findOneBy(['ticket' => $parentId, 'sla' => $slaId]);
        if (null === $ticketSla) {
            throw $this->createNotFoundException(
                'Ticket SLA for ticket ID='.$parentId.' and SLA ID='.$slaId.' not found'
            );
        }

        return parent::getAction($request, $ticketSla->getId());
    }

    /**
     * Delete single Ticket SLA.
     *
     * @ApiDoc(
     *    requirements={
     *        {
     *              "name"="parentId",
     *              "requirement"="\d+",
     *              "description"="the id of parent ticket",
     *              "dataType"="integer"
     *        },
     *        {
     *              "name"="slaId",
     *              "requirement"="\d+",
     *              "description"="the id of parent SLA",
     *              "dataType"="integer"
     *        }
     *     }
     * )
     *
     * @Rest\Delete("/by_sla/{slaId}")
     *
     * @param Request $request
     * @param int     $parentId parent Ticket's ID
     * @param int     $slaId    parent SLA's ID
     *
     * @return View
     */
    public function deleteSingleSlaAction(Request $request, $parentId, $slaId)
    {
        $ticketSla = $this->getRepository(TicketSla::class)->findOneBy(['ticket' => $parentId, 'sla' => $slaId]);
        if (null === $ticketSla) {
            throw $this->createNotFoundException(
                'Ticket SLA for ticket ID='.$parentId.' and SLA ID='.$slaId.' not found'
            );
        }

        return parent::deleteAction($ticketSla->getId(), $request);
    }

    /**
     * Create Ticket SLA.
     *
     * @ApiDoc(
     *    requirements={
     *        {
     *              "name"="parentId",
     *              "requirement"="\d+",
     *              "description"="the id of parent ticket",
     *              "dataType"="integer"
     *        },
     *        {
     *              "name"="sla",
     *              "requirement"="\d+",
     *              "description"="the id of parent SLA",
     *              "dataType"="integer"
     *        }
     *     },
     *     output="Application\DeskPRO\Entity\TicketSla"
     * )
     *
     * @Rest\Post("", name="api_ticket_sla_single_create")
     *
     * @param Request $request
     * @param int     $parentId parent ticket ID
     *
     * @return View
     */
    public function postSingleSlaAction(Request $request, $parentId)
    {
        $slaId     = $request->get('sla');
        $ticketSla = $this->getRepository(TicketSla::class)->findOneBy(['ticket' => $parentId, 'sla' => $slaId]);
        if (null !== $ticketSla) {
            return parent::putAction($ticketSla->getId(), $request);
        }

        return parent::postAction($request);
    }

    /**
     * Update Ticket SLA.
     *
     * @ApiDoc(
     *    requirements={
     *        {
     *              "name"="parentId",
     *              "requirement"="\d+",
     *              "description"="the id of parent ticket",
     *              "dataType"="integer"
     *        },
     *        {
     *              "name"="sla",
     *              "requirement"="\d+",
     *              "description"="the id of parent SLA",
     *              "dataType"="integer"
     *        }
     *     }
     * )
     *
     * @Rest\Put("", name="api_ticket_sla_single_update")
     *
     * @param Request $request
     * @param int     $parentId parent Ticket ID
     *
     * @return View
     */
    public function putSingleSlaAction(Request $request, $parentId)
    {
        $slaId     = $request->get('sla');
        $ticketSla = $this->getRepository(TicketSla::class)->findOneBy(['ticket' => $parentId, 'sla' => $slaId]);
        if (null === $ticketSla) {
            throw $this->createNotFoundException(
                'Ticket SLA for ticket ID='.$parentId.' and SLA ID='.$slaId.' not found'
            );
        }

        return parent::putAction($ticketSla->getId(), $request);
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketSla $entity
     */
    protected function deleteEntity($entity)
    {
        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeTicketSla($entity);

        $this->saveTicket($ticket);
    }
}
