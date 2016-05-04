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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\LegacyFilters;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Searcher\SearcherAbstract;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\TicketsPagerTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_filters")
 * @ApiDoc(
 *     target="all",
 *     section="Ticket filters (legacy)",
 *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LegacyTicketFilter"
 * )
 */
class TicketFiltersController extends CrudController
{
    use TicketsPagerTrait;

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
     * @return View
     */
    public function getFilterTicketsAction(Request $request, LegacyTicketFilter $filter)
    {
        $orderDir = $request->get('order_dir') === 'asc' ? SearcherAbstract::ORDER_ASC : SearcherAbstract::ORDER_DESC;
        $orderBy  = $request->get('order_by') ? 'ticket.'.$request->get('order_by') : '';

        $searcher = $this->get('data.ticket_legacy_filter_sets')->getFilterSearcher($filter);
        $searcher->setPersonContext($this->getUser());

        if ($orderBy) {
            $searcher->setOrderBy($orderBy, $orderDir);
        }

        $currentPage = $request->query->getInt('page', 1);
        $maxPerPage  = $request->query->getInt('count', self::$listPerPage);

        $total = $searcher->getCount();
        $ids   = $searcher->getMatches([
            'limit'  => $maxPerPage,
            'offset' => $maxPerPage * ($currentPage - 1),
        ]);

        return View::create($this->wrap($this->getTicketsPager($total, $ids, $currentPage, $maxPerPage)));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere('NOT REGEXP(e.sys_name, :regexp) = 1');
        $qb->setParameter('regexp', '^problem_[0-9]+$');
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var LegacyTicketFilter $entity */
        $entity = parent::findEntity($id, $request);
        if ($entity->isProblemFilter()) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
