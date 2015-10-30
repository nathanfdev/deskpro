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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketCountsController.
 */
class TicketCountsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a filter set count",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     * @Get("/ticket_filter_sets/{id}/count", name="api_ticket_filter_set_count")
     */
    public function getTicketFilterSetCountAction($id)
    {
        $set   = $this->findOr404('App:TicketFilterSet', $id);
        $count = $this->getFilterSetTicketsCount($set);

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a filter set count",
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="array"
     * )
     * @Get("/ticket_filter_sets/all/counts", name="api_ticket_filters_sets_counts")
     */
    public function getAllTicketFilterSetCountsAction()
    {
        $sets = $this->getManager()->getRepository('App:TicketFilterSet')->findAll();

        $filter_set_counts = [];
        foreach ($sets as $set) {
            $filter_set_counts[] = $this->getFilterSetTicketsCount($set);
        }

        return View::create(
            $this->dataSerialize(new PrimitiveArray($filter_set_counts)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a filter's count",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="group_by",
     *              "requirement"=".+",
     *              "description"="the grouping order you want",
     *              "dataType"="string",
     *              "required"=false
     *          },
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     * @Get("/ticket_filters/{id}/count")
     */
    public function getTicketFilterCountAction(Request $request, $id)
    {
        $filters = $this->get('data.filters');
        if (!$filter = $filters->getFilter($id)) {
            throw $this->createNotFoundException();
        }
        $group_by = $this->getTicketFilterGroupBy($filter);
        $count    = $this->getTicketFilterCount($filter, $group_by);

        return View::create($this->createRepresentation($count), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="get all filters counts",
     *      requirements={
     *          {
     *              "name"="group_by",
     *              "requirement"=".+",
     *              "description"="the grouping order you want",
     *              "dataType"="string",
     *              "required"=false
     *          },
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     * @Get("/ticket_filters_counts")
     */
    public function getAllTicketFilterCountsAction(Request $request)
    {
        $count    = Count::fromValue(0);
        $group_by = $request->get('group_by');

        /** @var TicketFilter[] $filters */
        $filters = $this->get('data.filters')->getFilters();
        foreach ($filters as $filter) {
            $filter_count = $this->getTicketFilterCount(
                $filter,
                isset($group_by[$filter->getId()]) ? $group_by[$filter->getId()] : null
            );
            $count->addNestedInstance($filter_count);
            $count->add($filter_count->getCount());
        }

        return View::create($this->createRepresentation($count), Response::HTTP_OK);
    }

    /**
     * @param TicketFilter $filter
     * @param sting        $group_by
     *
     * @return Count
     */
    private function getTicketFilterCount(TicketFilter $filter, $group_by = null)
    {
        $group_by or $group_by = $this->getTicketFilterGroupBy($filter);

        $engine  = $this->get('term_engine.dbal_ticket_filters.engine');
        $context = new TermEngineContext($this->getUser());
        if ($group_by) {
            $context->addGroupByFromString($group_by);
        }

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery $tickets_query */
        $tickets_query = $engine->evaluate($filter, $context);

        if ($group_by) {
            $filter_counts = $tickets_query->fetchGroupedCount();
            $filter_count  = Count::create(0, $filter->getId(), [], 'filter');
            foreach ($filter_counts as $nested_count) {
                $value = $nested_count['count'];
                unset($nested_count['count']);
                $group = array_pop($nested_count);
                $group = ctype_digit($group) ? (int) $group : $group;

                $filter_count->addNestedInstance(
                    Count::create($value, $group, [], $group_by),
                    true
                );
            }

            return $filter_count;
        } else {
            return Count::create($tickets_query->fetchCount(), $filter->getId(), [], 'filter');
        }
    }

    /**
     * Workhorse function for the count operations.
     *
     * @param TicketFilterSet $set
     *
     * @return array
     */
    private function getFilterSetTicketsCount(TicketFilterSet $set)
    {
        $total  = 0;
        $counts = [];

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine $engine */
        $engine = $this->get('term_engine.dbal_ticket_filters.engine');

        foreach ($set->getFilters() as $filter) {
            $filter_count = $this->getTicketFilterCount($filter);
            $counts[]     = $filter_count;
            $total += $filter_count->getCount();
        }

        return Count::create($total, $set->getId(), $counts);
    }

    /**
     * @param TicketFilter $filter
     *
     * @return string|null
     */
    private function getTicketFilterGroupBy(TicketFilter $filter)
    {
        $name    = TicketFiltersController::CUSTOM_FILTER_GROUP_BY_PREFIX.$filter->getId();
        $person  = $this->getUser();
        $setting = $this->getManager()->find(PersonSetting::class, compact('name', 'person'));

        return $setting ? $setting->getValue() : null;
    }
}
