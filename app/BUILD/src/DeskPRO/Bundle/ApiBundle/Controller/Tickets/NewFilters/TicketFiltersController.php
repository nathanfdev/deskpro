<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/new/ticket_filters")
 * @ApiDoc(target="all", section="Ticket filters", output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
 *      }
 *     }
 * )
 */
class TicketFiltersController extends CrudController
{
    public static $entity    = TicketFilter::class;
    public static $type      = TicketFilterType::class;
    public static $listOrder = 'asc';

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(array_merge($params, $masterRequest->query->all()), null, [
            '_controller' => 'ApiBundle:Tickets\NewFilters\TicketFilters:list',
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * See /tickets endpoint (Tickets section) docs for the parameter details.
     *
     * @ApiDoc(
     *     description="get filtered tickets",
     *     statusCodes={
     *         200="Returned with list of tickets",
     *         400="Returned in case of malformed request"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     *
     * @Rest\Get("/{filter}/tickets")
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @return Response
     */
    public function getTicketsAction(Request $request, TicketFilter $filter)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, [
            'filter' => $filter->getId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $set = $request->query->getInt('filter_set');
        if ($set) {
            $qb
                ->andWhere('e.filter_set = :filter_set')
                ->setParameter('filter_set', $set)
            ;
        }
    }
}
