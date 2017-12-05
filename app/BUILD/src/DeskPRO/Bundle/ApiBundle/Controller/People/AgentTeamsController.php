<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\AgentTeam;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\AgentTeamType;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AgentTeamsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/agent_teams")
 * @ApiDoc(target="all", section="Agents", output="DeskPRO\Bundle\AppBundle\Serializer\Model\AgentTeam")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\AgentTeamType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\AgentTeam"
 *      }
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     description="get list of teams",
 *     filters={
 *         {"name"="my", "pattern"="(1|0)", "description"="limit to my teams only", "dataType"="boolean"}
 *     },
 *     statusCodes={
 *         200="OK"
 *     }
 * )
 */
class AgentTeamsController extends CrudController
{
    public static $entity    = AgentTeam::class;
    public static $type      = AgentTeamType::class;
    public static $listOrder = 'asc';
    public static $listSort  = 'id';

    /**
     * Touching this endpoint will return a list of agents belongs to specified team.
     *
     * @ApiDoc(
     *      section="Agents",
     *      resourceDescription="Operations about agent`s teams",
     *      description="Return agents from team given team",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of team",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Will return when success",
     *          404="Returned when chat was not found",
     *          400="In all other cases except system error",
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person>"
     * )
     * @Rest\Get("/{id}/agents")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAgentsAction(Request $request, $id)
    {
        return PeopleController::subRequestSearch($this->getKernel(), $request, ['agent_team' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if ($request->query->getBoolean('my', false)) {
            $qb
                ->innerJoin("{$alias}.members", 'p', Join::WITH)
                ->andWhere('p.id = :me')
                ->setParameter('me', $this->getUser()->getId())
            ;
        }
    }
}
