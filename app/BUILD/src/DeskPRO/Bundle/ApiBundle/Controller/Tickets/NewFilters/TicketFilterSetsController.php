<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterSetType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to TicketFilterSet entities.
 *
 * @ApiModes("all")
 * @Rest\Route("/new/ticket_filter_sets")
 * @ApiDoc(target="all", section="Ticket filters", output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterSetType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
 *      }
 *     }
 * )
 */
class TicketFilterSetsController extends CrudController
{
    public static $entity    = TicketFilterSet::class;
    public static $type      = TicketFilterSetType::class;
    public static $listOrder = 'asc';

    /**
     * @ApiDoc(
     *      description="Get the filters within a filter set",
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
     *      output="array<DeskPRO\Bundle\AppBundle\Entity\TicketFilter>"
     * )
     * @Rest\Get("/{set}/filters")
     *
     * @param Request         $request
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function getFiltersAction(Request $request, TicketFilterSet $set)
    {
        return TicketFiltersController::subRequestSearch($this->getKernel(), $request, [
            'filter_set' => $set->getId(),
        ]);
    }
}
