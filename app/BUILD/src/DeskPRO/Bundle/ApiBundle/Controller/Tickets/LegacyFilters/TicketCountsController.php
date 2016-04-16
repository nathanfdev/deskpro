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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketCountsController.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Ticket filters (legacy)")
 */
class TicketCountsController extends AbstractLegacyFiltersController
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
     *     },
     *     filters={
     *          {
     *              "name"="group_by",
     *              "description"="[Ticket filter ID => group_by] map",
     *              "dataType"="array",
     *              "pattern"="\w+"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/ticket_filter_sets/{id}/count")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTicketFilterSetCountAction(Request $request, $id)
    {
        $set   = $this->getFilterSetOr404($id);
        $count = $this->getLegacyFilterSetService()->getFilterSetCount($set, $request->get('group_by'));

        return View::create($this->wrap($count));
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
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/ticket_filter_sets/all/counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterSetCountsAction(Request $request)
    {
        $sets   = $this->getLegacyFilterSetService()->getAllFilterSets();
        $counts = [];

        foreach ($sets as $set) {
            $counts[] = $this->getLegacyFilterSetService()->getFilterSetCount($set, $request->get('group_by'));
        }

        return View::create($this->wrap($counts));
    }

    /**
     * @ApiDoc(
     *     description="Get a filter's count",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the filter",
     *             "dataType"="integer"
     *         },
     *         {
     *             "name"="group_by",
     *             "requirement"=".+",
     *             "description"="the grouping order you want",
     *             "dataType"="string",
     *             "required"=false
     *         },
     *     },
     *     statusCodes={
     *         200="Success",
     *         404="Not Found"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/ticket_filters/{id}/count")
     *
     * @param Request            $request
     * @param LegacyTicketFilter $filter
     *
     * @return View
     */
    public function getTicketFilterCountAction(Request $request, LegacyTicketFilter $filter)
    {
        if ($filter->isProblemFilter()) {
            throw $this->createNotFoundException();
        }

        $groupBy = $request->get('group_by');
        $count   = $this->getLegacyFilterSetService()->getFilterCount($filter, $groupBy);

        return View::create($this->wrap($count));
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
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/ticket_filters_counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllTicketFilterCountsAction(Request $request)
    {
        $filters = $this->getLegacyFilterSetService()->getAllFilters();
        $count   = $this->getLegacyFilterSetService()->getFiltersCount(null, null, null, $filters, $request->get('group_by'));

        return View::create($this->wrap($count));
    }
}
