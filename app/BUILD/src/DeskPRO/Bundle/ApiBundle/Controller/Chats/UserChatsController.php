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

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\DateFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\QueryFilterContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserChatsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_chats")
 * @ApiDoc(target="all", section="Chats", output="Application\DeskPRO\Entity\ChatConversation")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="date_created", "dataType"="string", "pattern"="Y-m-d:Y-m-d"},
 *          {"name"="date_period", "dataType"="string", "pattern"="today|yesterday|etc"},
 *          {"name"="agent", "dataType"="integer", "pattern"="\d+"},
 *          {"name"="department", "dataType"="integer", "pattern"="\d+"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *          {"name"="group_by", "pattern"="date_created|date_period|agent|department", "description"="how to group counts", "dataType"="boolean"}
 *     }
 * )
 */
class UserChatsController extends CrudController
{
    use DateFiltersTrait;

    public static $exposeOnly = ['get', 'list', 'count', 'delete'];
    public static $entity     = ChatConversation::class;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new QueryFilterContext($qb, $alias, $request);
        $this->applyDateRangeFilters($context, 'date_created', 'created_from', 'created_to');

        $datePeriod = $request->get('date_period');
        if ($datePeriod) {
            $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
            $qb->andWhere("$datePeriodCaseWhen = :date_period");
            $qb->setParameter('date_period', $datePeriod);
        }

        $agent = $request->get('agent');
        if ($agent) {
            if ($agent === 'me') {
                $agent = $this->getUser()->getId();
            }

            $qb->andWhere($qb->expr()->eq("$alias.agent", ':agent'));
            $qb->setParameter('agent', $agent);
        }

        $department = $request->get('department');
        if ($department) {
            $qb->andWhere($qb->expr()->eq("$alias.department", ':department'));
            $qb->setParameter('department', $department);
        }

        // only user chats
        $qb->andWhere("$alias.is_agent = 0");
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'date_created':
                $qb
                    ->addSelect("DATE($alias.date_created) as group_name")
                    ->addSelect("DATE($alias.date_created) as title")
                    ->groupBy('group_name')
                ;

                break;
            case 'date_period':
                $datePeriodsDql = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                $qb
                    ->addSelect("$datePeriodsDql as group_name")
                    ->addSelect("$datePeriodsDql as title")
                    ->addSelect("FIELD($datePeriodsDql, 'today', 'yesterday', 'this_month', 'last_month', 'this_year', 'ever') as HIDDEN group_order")
                    ->orderBy('group_order')
                    ->groupBy('group_name')
                ;

                break;
            case 'agent':
                $qb
                    ->addSelect('g.id as group_name')
                    ->addSelect('g.name as title')
                    ->leftJoin("$alias.agent", 'g')
                    ->groupBy('group_name')
                ;

                break;
            case 'department':
                $qb
                    ->addSelect('g.id as group_name')
                    ->addSelect('g.title as title')
                    ->leftJoin("$alias.department", 'g')
                    ->groupBy('group_name')
                ;

                break;
        }
    }
}
