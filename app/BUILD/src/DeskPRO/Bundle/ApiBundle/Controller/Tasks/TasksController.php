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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\DateFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\LabelFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\QueryFilterContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\TaskType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TasksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tasks")
 * @ApiDoc(target="all", section="Tasks", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Task")
 * @ApiDoc(
 *     target="listAction",
 *     section="Tasks",
 *     description="get a list of tasks",
 *     filters={
 *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
 *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"},
 *         {"name"="label", "pattern"="(\w,)+", "description"="filter by labels", "dataType"="string"},
 *         {"name"="label_mode", "pattern"="+d+", "description"="additional filter for label, select where labels count > then you specified", "dataType"="string"},
 *         {"name"="project", "pattern"="(\d+),+", "description"="filter by project", "dataType"="string"},
 *         {"name"="creator", "pattern"="(\d+),+", "description"="filter by project", "dataType"="string"},
 *         {"name"="no_assignments", "pattern"="1|0", "description"="select only unassigned", "dataType"="boolean"},
 *         {"name"="assigned_agent", "pattern"="(\d+,)+", "description"="only where assigned agent has id", "dataType"="string"},
 *         {"name"="not_assigned_agent", "pattern"="(\d+,)+", "description"="only where assigned agent has no id", "dataType"="string"},
 *         {"name"="assigned_team", "pattern"="(\d+,)+", "description"="only where assigned team has id", "dataType"="string"},
 *         {"name"="not_assigned_team", "pattern"="(\d+,)+", "description"="only where assigned team has no id", "dataType"="string"},
 *         {"name"="assigned_department", "pattern"="(\d+,)+", "description"="only where assigned department has id", "dataType"="string"},
 *         {"name"="not_assigned_department", "pattern"="(\d+,)+", "description"="only where assigned department has no id", "dataType"="string"},
 *         {"name"="created_from", "pattern"="[a-zA-Z0-9\s-:]+", "description"="start of range to filter by created date", "dataType"="string"},
 *         {"name"="created_to", "pattern"="[a-zA-Z0-9\s-:]+", "description"="end of range to filter by created date", "dataType"="string"},
 *         {"name"="due_from", "pattern"="[a-zA-Z0-9\s-:]+", "description"="start of range to filter by due date", "dataType"="string"},
 *         {"name"="due_to", "pattern"="[a-zA-Z0-9\s-:]+", "description"="end of range to filter by due date", "dataType"="string"},
 *         {"name"="done_from", "pattern"="[a-zA-Z0-9\s-:]+", "description"="start of range to filter by done date", "dataType"="string"},
 *         {"name"="done_to", "pattern"="[a-zA-Z0-9\s-:]+", "description"="end of range to filter by done date", "dataType"="string"},
 *         {"name"="done", "pattern"="done", "description"="select only done|undone tasks", "dataType"="string"},
 *         {"name"="order_by", "pattern"="id|title|list|project|date_due|date_done|date_created|assignee", "description"="how to order", "dataType"="string"},
 *         {"name"="order_dir", "pattern"="asc|desc", "description"="order direction", "dataType"="string"},
 *
 *     },
 *     statusCodes={
 *         200="Returned if success"
 *     }
 * )
 */
class TasksController extends CrudController
{
    use LabelFiltersTrait, DateFiltersTrait;

    public static $entity      = Task::class;
    public static $type        = TaskType::class;
    public static $sortOptions = [
        'id'           => 'id',
        'title'        => 'title',
        'date_due'     => 'date_due',
        'date_done'    => 'date_done',
        'date_created' => 'date_created',
    ];

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
            '_controller' => 'ApiBundle:Tasks\Tasks:list',
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $done = $request->get('done');
        if ($request->query->has('done')) {
            $qb
                ->andWhere("$alias.is_done = :is_done")
                ->setParameter('is_done', (int) $done)
            ;
        }

        $project = $request->get('project');
        if ($project) {
            $qb
                ->andWhere("$alias.project IN (:project)")
                ->setParameter('project', $project)
            ;
        }

        $creator = $request->get('creator');
        if ($creator) {
            if ($creator === 'me') {
                $creator = $this->getUser()->getId();
            }

            $qb
                ->andWhere("$alias.creator IN (:creator)")
                ->setParameter('creator', $creator)
            ;
        }

        $context = new QueryFilterContext($qb, $alias, $request);

        if ($request->get('no_assignments')) {
            $qb
                ->leftJoin("$alias.assigned", 'ta')
                ->andWhere('ta.person IS NULL')
                ->andWhere('ta.team IS NULL')
                ->andWhere('ta.department IS NULL')
            ;
        } else {
            $this->applyAssignedFilter($context, 'person', 'assigned_agent');
            $this->applyAssignedFilter($context, 'person', 'not_assigned_agent');

            $this->applyAssignedFilter($context, 'team', 'assigned_team');
            $this->applyAssignedFilter($context, 'team', 'not_assigned_team');

            $this->applyAssignedFilter($context, 'department', 'assigned_department');
            $this->applyAssignedFilter($context, 'department', 'not_assigned_department');
        }

        $this->applyLabelFilters($context, Task::class);

        $this->applyDateRangeFilters($context, 'date_created', 'created_from', 'created_to');
        $this->applyDateRangeFilters($context, 'date_due', 'due_from', 'due_to');
        $this->applyDateRangeFilters($context, 'date_done', 'done_from', 'done_to');

        $qb->andWhere("$alias.for_del <> 1");
        $qb->addGroupBy("$alias.id");
    }

    /**
     * @param QueryFilterContext $context
     * @param string             $property
     * @param string             $queryParam
     */
    protected function applyAssignedFilter(QueryFilterContext $context, $property, $queryParam)
    {
        $alias = $context->getAlias();
        $value = $context->getRequest()->get($queryParam);
        $value = (array) $value;

        if (($key = array_search('me', $value)) !== false) {
            $user = $this->getUser();
            unset($value[$key]);

            switch ($property) {
                case 'person':
                    $value[] = $user->getId();
                    break;
                case 'team':
                    $user->loadHelper('AgentTeam');
                    $value = array_merge($value, $user->getAgentTeamIds() ?: []);
                    break;
                case 'department':
                    $user->loadHelper('AgentPermissions');
                    $value = array_merge($value, $user->getAllowedDepartments() ?: []);
                    break;
            }
        }

        if (!empty($value)) {
            $qb = $context->getQb();

            if (strpos($queryParam, 'not_') === 0) {
                $subAlias     = $queryParam;
                $subJoinAlias = $queryParam.'ta';

                $qb2 = $this->getManager()->createQueryBuilder();
                $qb2
                    ->select($subAlias)
                    ->from(static::$entity, $subAlias)
                    ->leftJoin("$subAlias.assigned", $subJoinAlias)
                    ->where(
                        "$alias.id = $subAlias.id",
                        "$subJoinAlias.$property IN (:$queryParam)"
                    )
                ;

                $qb->andWhere("NOT EXISTS ({$qb2->getDQL()})");
            } else {
                if (!in_array('ta', $qb->getAllAliases())) {
                    $qb->leftJoin("$alias.assigned", 'ta');
                }

                $qb->andWhere("ta.$property IN (:$queryParam)");
            }

            $qb->setParameter($queryParam, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting(QueryBuilder $qb, $alias, Request $request)
    {
        $orderBy  = $request->get('order_by');
        $orderDir = $request->get('order_dir');

        if ($orderDir && !in_array($orderDir, ['asc', 'desc'])) {
            throw $this->createBadRequestException('Unknown order value');
        }

        switch ($orderBy) {
            case 'list':
                $qb
                    ->leftJoin("$alias.list", 'list')
                    ->orderBy('list.title', $orderDir)
                    ->addOrderBy("$alias.display_order", 'ASC')
                ;

                break;
            case 'project':
                $qb
                    ->leftJoin("$alias.project", 'p')
                    ->orderBy('p.title', $orderDir)
                ;

                break;
            case 'assignee':
                $qb
                    ->leftJoin('ta.person', 'person')
                    ->leftJoin('ta.team', 'team')
                    ->leftJoin('ta.department', 'department')
                    ->orderBy('person.name', $orderDir)
                    ->addOrderBy('team.name', $orderDir)
                    ->addOrderBy('department.title', $orderDir)
                ;

                break;
            default:
                parent::applySorting($qb, $alias, $request);
        }
    }
}
