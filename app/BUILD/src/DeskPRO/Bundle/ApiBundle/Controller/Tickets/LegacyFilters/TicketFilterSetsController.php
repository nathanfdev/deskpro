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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class TicketFilterSetsController.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Ticket filters (legacy)")
 */
class TicketFilterSetsController extends AbstractLegacyFiltersController
{
    /**
     * Get the list of ticket filter sets available.
     *
     * @ApiDoc(
     *     description="get filter sets",
     *     statusCodes={
     *         200="Returned with the list if filters"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSet>"
     * )
     *
     * @Rest\Get("/ticket_filter_sets")
     */
    public function listAction()
    {
        return View::create($this->wrap($this->getLegacyFilterSetService()->getAllFilterSets()));
    }

    /**
     * Get a filter set with given id.
     *
     * @ApiDoc(
     *     description="get a filter set",
     *
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the filter set",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Everything is OK, we found your filter set",
     *         404="Filter set with provide ID wasn't found"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSet"
     * )
     *
     * @Rest\Get("/ticket_filter_sets/{id}")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        return View::create($this->wrap($this->getFilterSetOr404($id)));
    }

    /**
     * Get the filters within a filter set.
     *
     * **note: that could be done with sideloading**
     *
     * @ApiDoc(
     *     description="get filters belong to filter set",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the filter set",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Everything is OK, here is your filters",
     *         404="Filter set with provide ID wasn't found"
     *     },
     *     output="array<Application\DeskPRO\Entity\TicketFilter>"
     * )
     * @Rest\Get("/ticket_filter_sets/{id}/filters")
     *
     * @param int $id
     *
     * @return View
     */
    public function getSetFiltersAction($id)
    {
        return View::create($this->wrap($this->getFilterSetOr404($id)->getFilters()));
    }
}
