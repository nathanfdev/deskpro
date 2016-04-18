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

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to feedback.
 *
 * @ApiModes("all")
 * @Rest\Route("/feedback")
 * @ApiDoc(target="all", section="Feedback", output="Application\DeskPRO\Entity\Feedback")
 * @ApiDoc(
 *     target="listAction",
 *     resourceDescription="Operations about feedback",
 *     tags={"feedback"="#4422bb"},
 *     description="get a filtered list of feedback",
 *     filters={
 *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
 *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"},
 *         {"name"="awaiting_validation", "pattern"="1", "description"="select feedback awaiting validation only", "dataType"="boolean"},
 *         {"name"="status", "pattern"="active|closed|hidden", "description"="filter by status", "dataType"="string"},
 *         {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="limit with hidden_status"},
 *         {"name"="status_category", "pattern"="\w|[\w]", "description"="filter by status category", "dataType"="string[]"},
 *         {"name"="category", "pattern"="\w|[\w]", "description"="category title, or titles array", "dataType"="string[]"},
 *         {"name"="custom_category", "pattern"="\w|[\w]", "description"="filter by custom category", "dataType"="string[]"},
 *         {"name"="labels_mode", "pattern"="any|all", "description"="how to load labels", "dataType"="string"},
 *         {"name"="label", "pattern"="\w,\w...\w", "description"="select feedback with given lables", "dataType"="string"},
 *         {"name"="no_labels", "pattern"="1", "description"="select feedback have no label", "dataType"="boolean"},
 *         {"name"="ids", "pattern"="\d,\d...\d", "description"="comma separated ids list", "dataType"="string"},
 *         {"name"="created_from", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s start", "dataType"="date"},
 *         {"name"="created_to", "pattern"="YYYY-mm-dd H:i:s", "description"="lmit by date, interval`s end", "dataType"="date"},
 *         {"name"="order_dir", "pattern"="date_created|total_rating|num_ratings|id|title|status|category|person", "description"="how to order result", "dataType"="string"},
 *         {"name"="order_by", "pattern"="asc|desc", "description"="order direction", "dataType"="string"},
 *     },
 *     statusCodes={
 *         200="Returned if successful request",
 *         400="Returned if you filter set was malformed",
 *     }
 * )
 */
class FeedbackController extends AbstractFeedbackController
{
    public static $exposeOnly = ['get', 'list', 'delete'];
    public static $entity     = Feedback::class;

    /**
     * With this endpoint you can fetch the list of feedback counts filtered and grouped with various options.
     *
     * @ApiDoc(
     *     section="Feedback",
     *     resourceDescription="Operations about feedback",
     *     tags={"feedback"="#4422bb"},
     *     description="get feedback counts",
     *     statusCodes={
     *         200="Returned if successful request",
     *         400="Returned if you filter set was malformed",
     *     },
     *     filters={
     *         {"name"="group_by", "pattern"="status_category|hidden_status|category|custom_category", "description"="how to group counts", "dataType"="boolean"},
     *         {"name"="awaiting_validation", "pattern"="1", "description"="select feedback awaiting validation only", "dataType"="boolean"},
     *         {"name"="status", "pattern"="active|closed|hidden", "description"="filter by status", "dataType"="string"},
     *         {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="limit with hidden_status"},
     *         {"name"="status_category", "pattern"="\w|[\w]", "description"="filter by status category", "dataType"="string[]"},
     *         {"name"="category", "pattern"="\w|[\w]", "description"="category title, or titles array", "dataType"="string[]"},
     *         {"name"="custom_category", "pattern"="\w|[\w]", "description"="filter by custom category", "dataType"="string[]"},
     *         {"name"="labels_mode", "pattern"="any|all", "description"="how to load labels", "dataType"="string"},
     *         {"name"="label", "pattern"="\w,\w...\w", "description"="select feedback with given lables", "dataType"="string"},
     *         {"name"="no_labels", "pattern"="1", "description"="select feedback have no label", "dataType"="boolean"},
     *         {"name"="ids", "pattern"="\d,\d...\d", "description"="comma separated ids list", "dataType"="string"},
     *         {"name"="created_from", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s start", "dataType"="date"},
     *         {"name"="created_to", "pattern"="YYYY-mm-dd H:i:s", "description"="lmit by date, interval`s end", "dataType"="date"},
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getCountsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, new PermissionGroupContext(Feedback::class));

        $qb = $this->getManager()->createQueryBuilder();
        $qb->from(static::$entity, 'e');

        $this->applyListFilters($qb, 'e', $request);
        $this->applyListGroupBy($qb, 'e', $request);

        if (empty($qb->getDQLPart('groupBy'))) {
            $result = $qb->select('count(e.id)')->getQuery()->getSingleScalarResult();
            $count  = Count::fromValue($result);
        } else {
            $groupBy = $request->get('group_by');
            $result  = $qb->addSelect('count(e.id) as value')->getQuery()->getArrayResult();
            $count   = Count::fromGroupedBy($groupBy);

            foreach ($result as $group) {
                $count->addNested($group['value'], $group['id'], null, $group['group_name'], true);
            }
        }

        return new View($this->wrap($count));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $this->applyNotReviewedFilters($qb, $alias, $request);
        $this->applyDateCreatedFilters($qb, $alias, $request);
        $this->applyFeedbackListFilters($qb, $alias, $request);

        $qb->groupBy("$alias.id");
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    private function applyListGroupBy(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->resetDQLPart('groupBy');

        $groupBy = $request->get('group_by');
        if ($groupBy) {
            switch ($groupBy) {
                case 'hidden_status':
                    $qb
                        ->addSelect("{$alias}.hidden_status as group_name")
                        ->addSelect("{$alias}.hidden_status as id")
                        ->andWhere("{$alias}.hidden_status IS NOT NULL")
                        ->andWhere("{$alias}.hidden_status <> ''")
                        ->groupBy('group_name')
                    ;

                    break;
                case 'custom_category':
                    $qb
                        ->leftJoin("{$alias}.custom_data", 'customCat')
                        ->leftJoin('customCat.field', 'def')
                        ->addSelect('customCat.input as group_name')
                        ->addSelect('customCat.id as id')
                        ->andWhere('def.sys_name = :cat')
                        ->setParameter('cat', 'cat')
                        ->groupBy('group_name')
                    ;

                    break;
                case 'category':
                    $qb
                        ->join("{$alias}.category", 'category')
                        ->addSelect('category.title as group_name')
                        ->addSelect('category.id as id')
                        ->groupBy('group_name')
                    ;

                    break;
                case 'status_category':
                    $qb
                        ->leftJoin("{$alias}.status_category", 'statusCategory')
                        ->addSelect('statusCategory.title as group_name')
                        ->addSelect('statusCategory.id as id')
                        ->groupBy('group_name')
                    ;

                    break;
                default:
                    throw $this->createBadRequestException();
            }
        }
    }
}
