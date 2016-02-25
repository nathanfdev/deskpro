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

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API access to TicketFilterSet entities.
 *
 * @ApiModes("all")
 * @Route("/new/ticket_filter_sets")
 */
class TicketFilterSetsController extends CrudController
{
    public static $entity    = TicketFilterSet::class;
    public static $type      = 'filter_set';
    public static $listOrder = 'asc';

    /**
     * @ApiDoc(
     *      description="Reorder filter sets.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Post("/display_order")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postReorderAction(Request $request)
    {
        $data = $request->request->all();
        if (!is_array($data) || !isset($data['display_order'])) {
            throw new NotFoundHttpException();
        }

        $results = [];
        foreach ($data['display_order'] as $order => $filter_set_id) {
            $filter_set = $this->getRepository('App:TicketFilterSet')->find($filter_set_id);
            if (!$filter_set) {
                continue;
            }

            $filter_set->setDisplayOrder($order);
            $this->getManager()->persist($filter_set);
            $results[$order] = $filter_set_id;
        }

        $this->getManager()->flush();

        return View::create($this->dataSerialize($results));
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
     * @Get("/{set}/filters")
     *
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function getFiltersAction(TicketFilterSet $set)
    {
        return View::create($this->dataSerialize($set->getFilters()));
    }
}
