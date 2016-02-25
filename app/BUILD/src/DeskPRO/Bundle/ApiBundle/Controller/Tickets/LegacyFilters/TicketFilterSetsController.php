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
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class TicketFilterSetsController.
 *
 * @ApiModes("all")
 */
class TicketFilterSetsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get the list of ticket filter sets available",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_filter_sets")
     */
    public function cgetAction()
    {
        $data_service = $this->get('data.ticket_legacy_filter_sets');
        $filter_sets  = $data_service->getAllFilterSets();

        return View::create($this->dataSerialize($filter_sets));
    }

    /**
     * @ApiDoc(
     *      description="Get a filter set",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter set",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
     * )
     *
     * @Get("/ticket_filter_sets/{id}")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $data_service = $this->get('data.ticket_legacy_filter_sets');
        $filter_set   = $data_service->getFilterSet($id);

        if (!$filter_set) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($filter_set));
    }

    /**
     * @ApiDoc(
     *      description="Get the filters within a filter set",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter set",
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
     * @Get("/ticket_filter_sets/{id}/filters")
     *
     * @param int $id
     *
     * @return View
     */
    public function getSetFiltersAction($id)
    {
        return View::create([]);
    }
}
