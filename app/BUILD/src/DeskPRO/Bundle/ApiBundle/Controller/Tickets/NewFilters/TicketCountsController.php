<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketCountsDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketCountsController.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Ticket filters")
 */
class TicketCountsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get a filter set count",
     *      requirements={
     *          {
     *              "name"="set",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *     },
     *     filters={
     *          {
     *              "name"="group_by",
     *              "description"="[Ticket filter ID => group_by] map",
     *              "pattern"="\d+",
     *              "dataType"="array"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Returned if set was not found"
     *      },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/new/ticket_filter_sets/{set}/count")
     *
     * @param Request         $request
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function getTicketFilterSetCountAction(Request $request, TicketFilterSet $set)
    {
        $count = $this->getCountsService()->getFilterSetCount($set, $request->get('group_by'));

        return View::create($this->wrap($count));
    }

    /**
     * @ApiDoc(
     *      description="Get all filter set counts",
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      filters={
     *          {
     *              "name"="group_by",
     *              "description"="[Ticket filter ID => group_by] map",
     *              "pattern"="\w+",
     *              "dataType"="array"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *      },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/new/ticket_filter_sets/all/counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterSetCountsAction(Request $request)
    {
        /** @var TicketFilterSet[] $sets */
        $sets   = $this->getRepository(TicketFilterSet::class)->findAll();
        $counts = [];

        foreach ($sets as $set) {
            $counts[] = $this->getCountsService()->getFilterSetCount($set, $request->get('group_by'));
        }

        return View::create($this->wrap($counts));
    }

    /**
     * @ApiDoc(
     *      description="Get a filter's count",
     *      requirements={
     *          {
     *              "name"="filter",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *     },
     *     filters={
     *          {
     *              "name"="group_by",
     *              "pattern"=".+",
     *              "description"="the grouping order you want",
     *              "dataType"="string",
     *          },
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Returned if filter was not found"
     *      },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/new/ticket_filters/{filter}/count")
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @return View
     */
    public function getTicketFilterCountAction(Request $request, TicketFilter $filter)
    {
        $count = $this->getCountsService()->getFilterCount($filter, $request->get('group_by'));

        return View::create($this->wrap($count));
    }

    /**
     * @ApiDoc(
     *      description="Get all filters counts",
     *      filters={
     *          {
     *              "name"="group_by",
     *              "requirement"=".+",
     *              "description"="[Ticket filter ID => group_by] map",
     *              "dataType"="string",
     *              "required"=false
     *          },
     *      },
     *      statusCodes={
     *          200="Success",
     *      },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/new/ticket_filters_counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterCountsAction(Request $request)
    {
        $count   = Count::fromValue(0);
        $groupBy = $request->get('group_by');

        /** @var TicketFilter[] $filters */
        $filters = $this->getRepository(TicketFilter::class)->findAll();
        foreach ($filters as $filter) {
            $filterCount = $this->getCountsService()->getFilterCount(
                $filter,
                isset($groupBy[$filter->getId()]) ? $groupBy[$filter->getId()] : null
            );

            $count->addNestedInstance($filterCount, true);
        }

        return View::create($this->wrap($count));
    }

    /**
     * @return TicketCountsDataService
     */
    private function getCountsService()
    {
        return $this->get('data.tickets.ticket_counts');
    }
}
