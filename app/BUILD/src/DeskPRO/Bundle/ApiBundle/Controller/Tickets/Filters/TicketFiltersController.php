<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\CountBadge\CountBuilder;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\ORM\QueryBuilder;
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
    public static $exposeOnly = ['list', 'get'];
    public static $entity     = TicketFilter::class;
    public static $listOrder  = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        /** @var Person $person */
        $person = $this->getUser();

        if ($request->query->getBoolean('enabled')) {
            $qb->andWhere("$alias.isEnabled = true");
        }

        if (!$person->isAdmin() || $request->query->getBoolean('mine')) {
            $qb->leftJoin("$alias.filterSetLinks", 'filterSetLink');
            $qb->leftJoin('filterSetLink.filterSet', 'filterSet');
            $qb->leftJoin('filterSet.sharedAgents', 'agents');

            $personWhere = $qb->expr()->orX(
                'filterSet.isGlobal = 1',
                'agents = :person'
            );

            if ($person->getTeams()->count()) {
                $qb->leftJoin('filterSet.sharedTeams', 'teams');
                $personWhere->add('teams IN (:teams)');
                $qb->setParameter('teams', $person->getTeams());
            }

            $qb->andWhere($personWhere)->setParameter('person', $person);
        }
    }

    /**
     * @ApiDoc(
     *     description="Get filter's tickets. See /tickets endpoint docs for the parameter details.",
     *     requirements={
     *         {
     *             "name"="order_by",
     *             "requirement"=".+",
     *             "description"="Specify a field to order by, and optionally direction. Example: ticket.date_created:desc",
     *             "dataType"="string",
     *             "required"=false
     *         }
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
        $orderBy  = $request->get('order_by') ? $request->get('order_by') : null;
        $orderDir = 'ASC';

        if ($orderBy && strpos($orderBy, ':') !== false) {
            list($orderBy, $orderDir) = explode(':', $orderBy);
        }

        $offset     = $request->query->getInt('offset');
        $maxPerPage = $request->query->getInt('count', self::$listPerPage);

        $ticketFilters = $this->container->get('ticketfilters');

        try {
            $query = $ticketFilters->getFilterQuery($ticketFilter->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get filter model');
        }

        try {
            $context = $ticketFilters->getAgentContext($this->getUser()->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get agent model');
        }

        $searchParams = $ticketFilters->createSearchParams();
        if ($orderBy) {
            $searchParams->orderBy($orderBy, $orderDir);
        }

        $searcher = $ticketFilters->getSearcher();
        $qb       = $searcher
            ->getIdsQueryBuilder($query, $context)
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
     *             "description"="the grouping you want",
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

        $ticketFilters = $this->container->get('ticketfilters');

        try {
            $query = $ticketFilters->getFilterQuery($ticketFilter->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get filter model');
        }

        try {
            $context = $ticketFilters->getAgentContext($this->getUser()->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get agent model');
        }

        $searchParams = $ticketFilters->createSearchParams();
        if ($groupBy) {
            $groupBy = explode(',', $groupBy);
            foreach ($groupBy as $g) {
                $searchParams->groupBy($g);
            }
        }

        $searcher = $ticketFilters->getSearcher();

        if (!$searchParams->hasGroupFields()) {
            $countInt = $searcher
                ->getCountQueryBuilder($query, $context, $searchParams)
                ->execute()
                ->fetchColumn();

            $count = Count::create(
                $countInt,
                $ticketFilter->getId(),
                'filter',
                $ticketFilter->getTitle()
            );
        } else {
            $countInts = $searcher
                ->getCountQueryBuilder($query, $context, $searchParams)
                ->execute()
                ->fetchAll(\PDO::FETCH_ASSOC);

            $groupFields = $searchParams->getGroupFields();

            // CountBuilder expects key names to be the name of the grouping field
            $countRekeyed = ListUtils::map($countInts, function (array $count) use ($groupFields) {
                $newCount = ['count' => $count['count']];
                foreach ($groupFields as $idx => $fieldId) {
                    $key = 'group_field'.$idx;
                    $titleKey = $count[$key];
                    if ($titleKey === null) {
                        // special key used to look up self
                        $titleKey = null;
                    } elseif ($titleKey === 0 || $titleKey === '0') {
                        // -1 represents null -- used to distinguish it
                        // from no value i.e. user clciks top-level label means no sub-filtering at all
                        // but clicking 'None' means searching for a null value
                        $titleKey = -1;
                    }
                    $newCount[$fieldId] = $titleKey;
                }

                return $newCount;
            });

            $titleResolver = $this->get('ticketfilters.count_title_resolver');

            $b = new CountBuilder($titleResolver);
            if ($countRekeyed) {
                $count = $b->buildFromArray($countRekeyed, $searchParams->getGroupFields());
                $count->setId($ticketFilter->getId());
                $count->setTitle($ticketFilter->getTitle());
                $count->setType('filter');
            } else {
                // Failover when filter is empty
                $count = Count::create(
                    0,
                    $ticketFilter->getId(),
                    'filter',
                    $ticketFilter->getTitle()
                );
            }
        }

        return View::create($this->wrap($count));
    }
}
