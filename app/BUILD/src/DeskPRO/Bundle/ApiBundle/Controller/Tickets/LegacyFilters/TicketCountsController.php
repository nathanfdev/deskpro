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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\LegacyFilters;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\Filters;
use Application\DeskPRO\Tickets\GroupingCounter;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketCountsController.
 *
 * @ApiModes("all")
 */
class TicketCountsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get a filter set count",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="group_by",
     *              "description"="[Ticket filter ID => group_by] map",
     *              "dataType"="array"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     * @Get("/ticket_filter_sets/{set}/count")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTicketFilterSetCountAction(Request $request, $id)
    {
        return View::create([]);
    }

    /**
     * @ApiDoc(
     *      description="Get all filter set counts",
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      requirements={
     *          {
     *              "name"="group_by",
     *              "description"="[Ticket filter ID => group_by] map",
     *              "dataType"="array"
     *          }
     *      },
     *      output="array"
     * )
     * @Get("/ticket_filter_sets/all/counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterSetCountsAction(Request $request)
    {
        return View::create([]);
    }

    /**
     * @ApiDoc(
     *      description="Get a filter's count",
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
     *
     * @param Request            $request
     * @param LegacyTicketFilter $ticket_filter
     *
     * @return View
     */
    public function getTicketFilterCountAction(Request $request, LegacyTicketFilter $ticket_filter)
    {
        $group_by = $request->get('group_by');
        $count    = $this->getTicketFilterCount($ticket_filter, $group_by);

        return View::create($count);
    }

    /**
     * @ApiDoc(
     *      description="Get all filters counts",
     *      requirements={
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
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     * @Get("/ticket_filters_counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterCountsAction(Request $request)
    {
        $count       = Count::fromValue(0);
        $group_by    = $request->get('group_by');
        $filters     = new Filters();
        $all_filters = $filters->getFiltersForPerson($this->getUser());

        foreach ($all_filters as $filter) {
            $filter_group_by = !empty($group_by[$filter->getId()]) ? $group_by[$filter->getId()] : null;
            $count->addNestedInstance($this->getTicketFilterCount($filter,  $filter_group_by), true);
        }

        return View::create($count);
    }

    /**
     * @param LegacyTicketFilter $filter
     * @param string|null        $group_by
     *
     * @return Count
     */
    protected function getTicketFilterCount(LegacyTicketFilter $filter, $group_by = null)
    {
        /** @var Person $user */
        $user = $this->getUser();
        $user->loadHelper('AgentTeam');
        $user->loadHelper('AgentPermissions');

        $searcher = $filter->getSearcher();
        $searcher->setPersonContext($user);

        if ($group_by) {
            $ticket_ids = $searcher->getMatches();

            $grouper = new GroupingCounter();
            $grouper->setGrouping($group_by);
            $grouper->setMode('specify', $ticket_ids);

            $grouped_info = $grouper->getDisplayArray();
            $total_info   = array_shift($grouped_info['items']);
            $filter_count = Count::create($total_info['count'], $filter->getId(), $filter->getRawTitle(), 'filter', $group_by);

            foreach ($grouped_info['items'] as $nested_item) {
                $filter_count->addNested($nested_item['total'], $nested_item['id'], $group_by, isset($nested_item['title']) ? $nested_item['title'] : null, true);
            }
        } else {
            $filter_count = Count::create($searcher->getCount(), $filter->getId(), $filter->getRawTitle(), 'filter');
        }

        return $filter_count;
    }
}
