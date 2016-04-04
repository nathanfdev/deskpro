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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\LegacyFilters;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Searcher\SearcherAbstract;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketFiltersController.
 *
 * @ApiDocSection("Ticket filters (legacy)")
 * @ApiModes("all")
 * @Rest\Route("/ticket_filters")
 */
class TicketFiltersController extends CrudController
{
    public static $exposeOnly = ['list', 'get'];
    public static $entity     = LegacyTicketFilter::class;
    public static $listOrder  = 'asc';

    /**
     * @ApiDoc(
     *      description="Get filter's tickets. See /tickets endpoint docs for the parameter details.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Rest\Get("/{filter}/tickets")
     *
     * @param Request            $request
     * @param LegacyTicketFilter $filter
     *
     * @return Response
     */
    public function getFilterTicketsAction(Request $request, LegacyTicketFilter $filter)
    {
        $orderDir = $request->get('order') === 'asc' ? SearcherAbstract::ORDER_ASC : SearcherAbstract::ORDER_DESC;
        $orderBy  = $request->get('sort') ? 'ticket.'.$request->get('sort') : '';

        $searcher = $this->get('data.ticket_legacy_filter_sets')->getFilterSearcher($filter);
        $searcher->setPersonContext($this->getUser());

        if ($orderBy) {
            $searcher->setOrderBy($orderBy, $orderDir);
        }

        $currentPage = $request->query->getInt('page', 1);
        $maxPerPage  = $request->query->getInt('count', self::$listPerPage);

        $ticketIds = $searcher->getMatches([
            'limit'  => $maxPerPage,
            'offset' => $maxPerPage * ($currentPage - 1),
        ]);

        $tickets = $this->getRepository(Ticket::class)->findBy(['id' => $ticketIds]);
        $pager   = new Pagerfanta(new FixedAdapter($searcher->getCount(), $tickets));

        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return View::create($this->wrap($pager));
    }
}
