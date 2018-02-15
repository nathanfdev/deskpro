<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_filters2")
 * @ApiDoc(
 *     target="all",
 *     section="Ticket filters (new)",
 *     output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
 * )
 */
class TicketFiltersController extends CrudController
{
    public static $exposeOnly = ['list', 'get', 'count'];
    public static $entity     = TicketFilter::class;
    public static $listSort   = 'displayOrder';
    public static $listOrder  = 'asc';

    /**
     * @ApiDoc(
     *     description="Get filter's tickets. See /tickets endpoint docs for the parameter details.",
     *     filters={
     *         {
     *             "name"="sort",
     *             "description"="tickets list sort",
     *             "pattern"="id|urgency|date_created|date_last_agent_reply|date_last_user_reply|date_last_reply|date_user_waiting|total_user_waiting",
     *             "dataType"="string",
     *         },
     *         {"name"="order", "description"="tickets list sort order", "dataType"="string", "pattern"="asc|desc"}
     *     },
     *     statusCodes={
     *         200="Success"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     *
     * @Rest\Get("/{ticketFilter}/tickets")
     *
     * @param Request            $request
     * @param LegacyTicketFilter $ticketFilter
     *
     * @return View
     */
    public function getFilterTicketsAction(Request $request, TicketFilter $ticketFilter)
    {
        // ordering
        $orderDir = $request->get('order_dir') === 'asc' ? 'ASC' : 'DESC';
        $orderBy  = $request->get('order_by') ? 'tickets.'.$request->get('order_by') : 'tickets.id';

        $offset     = $request->query->getInt('offset');
        $maxPerPage = $request->query->getInt('count', self::$listPerPage);

        $loader  = $this->container->get('ticketfilters.loader');
        $meAgent = $loader->getAgentById($this->getUser()->getId());
        if (!$meAgent) {
            throw $this->createNotFoundException('failed to get agent model');
        }
        $context = new Context($meAgent);
        $filter  = $loader->getFilterById($ticketFilter->getId());
        if (!$filter) {
            throw $this->createNotFoundException('failed to get filter model');
        }

        $searcher = $this->container->get('ticketfilter.ticket_sql_searcher');
        $qb       = $searcher
            ->getIdsQueryBuilder($filter->query, $context)
            ->setFirstResult($offset)
            ->setMaxResults($maxPerPage + 1);

        $ids     = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);
        $tickets = $this->getRepository(Ticket::class)->getByIds($ids);

        if (count($ids) > $maxPerPage) {
            $hasMore = true;
            array_pop($tickets); // we got +1 as an indicator that there are more
        } else {
            $hasMore = false;
        }

        return View::create($this->wrap($tickets, [
            'offset'     => $offset,
            'perPage'    => $maxPerPage,
            'hasMore'    => $hasMore,
            'nextOffset' => $offset + $maxPerPage,
        ]));
    }

    /**
     * @ApiDoc(
     *     description="Get filter's count",
     *     requirements={
     *         {
     *             "name"="group_by",
     *             "requirement"=".+",
     *             "description"="the grouping order you want",
     *             "dataType"="string",
     *             "required"=false
     *         },
     *     },
     *     statusCodes={
     *         200="Success"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     *
     * @Rest\Get("/{ticketFilter}/count")
     *
     * @param Request            $request
     * @param LegacyTicketFilter $ticketFilter
     *
     * @return View
     */
    public function getFilterCountAction(Request $request, TicketFilter $ticketFilter)
    {
        $groupBy = $request->get('group_by');

        $loader  = $this->container->get('ticketfilters.loader');
        $meAgent = $loader->getAgentById($this->getUser()->getId());
        if (!$meAgent) {
            throw $this->createNotFoundException('failed to get agent model');
        }
        $context = new Context($meAgent);
        $filter  = $loader->getFilterById($ticketFilter->getId());
        if (!$filter) {
            throw $this->createNotFoundException('failed to get filter model');
        }

        $searcher = $this->container->get('ticketfilter.ticket_sql_searcher');
        $countInt = $searcher
            ->getCountQueryBuilder($filter->query, $context)
            ->execute()
            ->fetchColumn();

        $count = Count::create(
            $countInt,
            $filter->id,
            'filter',
            $ticketFilter->getTitle()
        );

        return View::create($this->wrap($count));
    }
}
