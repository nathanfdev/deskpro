<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiModes("all")
 * @Rest\Route("/ticket_filters2_sets")
 * @ApiDoc(
 *     target="all",
 *     section="Ticket filters (new)",
 *     output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
 * )
 */
class TicketFilterSetsController extends CrudController
{
    public static $exposeOnly   = ['list', 'get'];
    public static $entity       = TicketFilterSet::class;
    public static $listSort     = 'displayOrder';
    public static $listOrder    = 'asc';
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="mine", "pattern"="1|0", "description"="Only get filter sets you are added to. This only applies to admins; regular agents will only ever see their own anyway due to permissions.", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        /** @var Person $person */
        $person = $this->getUser();

        if (!$person->isAdmin() || $request->query->getBoolean('mine')) {
            $qb->leftJoin("$alias.sharedAgents", 'agents');

            $personWhere = $qb->expr()->orX(
                "$alias.isGlobal = 1",
                'agents = :person'
            );

            if ($person->getTeams()->count()) {
                $qb->leftJoin("$alias.sharedTeams", 'teams');
                $personWhere->add('teams IN (:teams)');
                $qb->setParameter('teams', $person->getTeams());
            }

            $qb->andWhere($personWhere)->setParameter('person', $person);
        }
    }
}
