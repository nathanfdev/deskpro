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

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Notifications\NewFeedbackNotification;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Feedback\FeedbackType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to feedback.
 *
 * @ApiModes("all")
 * @Rest\Route("/feedback")
 * @ApiDoc(target="all", section="Feedback", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
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
 *         {"name"="created_to", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s end", "dataType"="date"},
 *         {
 *              "name"="feedback_field.{id}",
 *              "description"="
 *                  Custom feedback field filter. To filter by a custom field with ID=1 you need to add
 *                  ?feedback_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *         {"name"="order_by", "pattern"="date_created|total_rating|num_ratings|id|title|status|category|person", "description"="how to order result", "dataType"="string"},
 *         {"name"="order_dir", "pattern"="asc|desc", "description"="order direction", "dataType"="string"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *         {"name"="group_by", "pattern"="status_category|hidden_status|category|custom_category", "description"="how to group counts", "dataType"="boolean"}
 *     }
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Feedback\FeedbackType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Feedback",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class FeedbackController extends AbstractFeedbackController
{
    public static $entity      = Feedback::class;
    public static $type        = FeedbackType::class;
    public static $sortOptions = [
        'date_created' => 'date_created',
        'total_rating' => 'total_rating',
        'num_ratings'  => 'num_ratings',
        'title'        => 'title',
        'status'       => 'status',
        'category'     => ['join' => 'category', 'as' => 'c', 'sort' => 'c.id'],
        'person'       => ['join' => 'person', 'as' => 'p', 'sort' => 'p.id'],
    ];

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\Feedback": "DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\FeedbackCsv"
     * })
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function csvAction(Request $request)
    {
        return $this->listAction($request);
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
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'hidden_status':
                $qb
                    ->addSelect("{$alias}.hidden_status as group_name")
                    ->addSelect("{$alias}.hidden_status as title")
                    ->andWhere("{$alias}.hidden_status IS NOT NULL")
                    ->andWhere("{$alias}.hidden_status <> ''")
                    ->groupBy('group_name');

                break;
            case 'custom_category':
                $qb
                    ->join("{$alias}.custom_data", 'customCat')
                    ->join('customCat.field', 'def')
                    ->join('def.parent', 'parent')
                    ->addSelect('def.title as title')
                    ->addSelect('def.id as group_name')
                    ->andWhere('parent.sys_name = :cat')
                    ->setParameter('cat', 'cat')
                    ->groupBy('group_name');

                break;
            case 'category':
                $qb
                    ->join("{$alias}.category", 'category')
                    ->addSelect('category.title as title')
                    ->addSelect('category.id as group_name')
                    ->groupBy('group_name');

                break;
            case 'status_category':
                $qb
                    ->join("{$alias}.status_category", 'statusCategory')
                    ->addSelect('statusCategory.title as title')
                    ->addSelect('statusCategory.id as group_name')
                    ->groupBy('group_name');

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'agent_interface' => true,
            'person'          => $this->getUser(),
        ]);

        $view = parent::handleForm($model, $request, $options);

        // if no exception has been the feedback has been created
        $notify = new NewFeedbackNotification($model);
        $notify->send();

        return $view;
    }
}
