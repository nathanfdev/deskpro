<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketMessageController.
 */
class TicketMessagesController extends CrudController
{
    public static $exposeOnly  = ['list', 'get'];
    public static $entity      = TicketMessage::class;
    public static $sortOptions = ['date' => 'date_created'];
    public static $listSort    = 'id';
    public static $listOrder   = 'asc';

    /**
     * @ApiDoc(
     *      description="Get a ticket message",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_messages/{id}", requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        return parent::getAction($id);
    }

    /**
     * @ApiDoc(
     *      description="Get list of ticket's messages",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/tickets/{id}/messages")
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $ticket = $this->findOr404(Ticket::class, $request->get('id'));
        $qb->andWhere("{$alias}.ticket = :ticket");
        $qb->setParameter('ticket', $ticket->getId());
    }
}
