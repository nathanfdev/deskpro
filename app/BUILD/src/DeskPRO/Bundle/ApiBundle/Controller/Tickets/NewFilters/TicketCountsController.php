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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketCountsDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
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
     * @Get("/new/ticket_filter_sets/{set}/count")
     *
     * @param Request         $request
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function getTicketFilterSetCountAction(Request $request, TicketFilterSet $set)
    {
        $count = $this->getCountsService()->getFilterSetTicketsCount($set, $request->get('group_by'));

        return View::create($this->dataSerialize($count));
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
     * @Get("/new/ticket_filter_sets/all/counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterSetCountsAction(Request $request)
    {
        $sets   = $this->getRepository('App:TicketFilterSet')->findAll();
        $counts = [];

        foreach ($sets as $set) {
            $counts[] = $this->getCountsService()->getFilterSetTicketsCount($set, $request->get('group_by'));
        }

        return View::create($this->dataSerialize($counts));
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
     * @Get("/new/ticket_filters/{filter}/count")
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @return View
     */
    public function getTicketFilterCountAction(Request $request, TicketFilter $filter)
    {
        $count = $this->getCountsService()->getTicketFilterCount($filter, $request->get('group_by'));

        return View::create($this->dataSerialize($count));
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
     * @Get("/new/ticket_filters_counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterCountsAction(Request $request)
    {
        $count    = Count::fromValue(0);
        $group_by = $request->get('group_by');

        /** @var TicketFilter[] $filters */
        $filters = $this->getRepository(TicketFilter::class)->findAll();
        foreach ($filters as $filter) {
            $filter_count = $this->getCountsService()->getTicketFilterCount(
                $filter,
                isset($group_by[$filter->getId()]) ? $group_by[$filter->getId()] : null
            );

            $count->addNestedInstance($filter_count);
            $count->add($filter_count->getCount());
        }

        return View::create($this->dataSerialize($count));
    }

    /**
     * @return TicketCountsDataService
     */
    private function getCountsService()
    {
        return $this->get('data.tickets.ticket_counts');
    }
}
