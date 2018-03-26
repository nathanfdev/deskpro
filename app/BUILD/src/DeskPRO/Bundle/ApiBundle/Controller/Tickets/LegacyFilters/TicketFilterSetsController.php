<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\LegacyFilters;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class TicketFilterSetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_filter_sets")
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
     * @Rest\Get("")
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
     * @Rest\Get("/{id}")
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
     * @Rest\Get("/{id}/filters")
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
