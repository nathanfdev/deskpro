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

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Get("/ticket_filter_sets/{set}/count", name="api_ticket_filter_set_count")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTicketFilterSetCountAction(Request $request, $id)
    {
        return View::create([], Response::HTTP_OK);
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
     * @Get("/ticket_filter_sets/all/counts", name="api_ticket_filters_sets_counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterSetCountsAction(Request $request)
    {
        return View::create([], Response::HTTP_OK);
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
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTicketFilterCountAction(Request $request, $id)
    {
        return View::create([], Response::HTTP_OK);
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
        $count    = Count::fromValue(0);
        $group_by = $request->get('group_by');

        $grouper = new \Application\DeskPRO\Tickets\GroupingCounter();
        $grouper->setGrouping($group_by);
        $grouper->setMode('specify', $ticket_batch['ticket_ids']);

        $all_counts = $filters_helper->getGroupedFiltersForPerson($this->getUser());

        $filters = [];
        foreach ($all_counts as $filter_group) {
            foreach ($filter_group as $filter) {
                $filters[] = $filter;
            }
        }

        return View::create($this->dataSerialize($filters), Response::HTTP_OK);
    }
}
