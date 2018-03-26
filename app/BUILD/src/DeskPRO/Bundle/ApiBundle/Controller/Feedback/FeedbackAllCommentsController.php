<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\FeedbackCommentType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to feedback comments.
 *
 * @ApiModes("all")
 * @Rest\Route("/feedback_comments")
 * @ApiDoc(target="all", section="Feedback", output="Application\DeskPRO\Entity\FeedbackComment")
 * @ApiDoc(
 *      target="listAction",
 *      tags={"feedback"="#4422bb", "comments"="#22aa22"},
 *      description="get list of feedback comments",
 *      statusCodes={
 *          200="Returned if everything is ok",
 *          400="Returned if your filters was invalid"
 *      },
 *      filters={
 *          {"name"="page", "type"="integer", "default"=1, "description"="current page"},
 *          {"name"="count", "type"="integer", "default"=5, "description"="per page comments quantity"},
 *          {"name"="awaiting_validation", "type"="boolean", "description"="set it if you want to fetch new comments"},
 *          {"name"="ids", "dataType"="string", "description"="a comma separated list of comment`s ids"},
 *          {"name"="category", "dataType"="string", "description"="category to search, exact name"},
 *          {"name"="statusCategory", "dataType"="integer", "description"="integer represents status category"},
 *          {"name"="label", "dataType"="string", "description"="a comma separated list of exact label names"},
 *          {"name"="no_labels", "dataType"="boolean", "description"="boolean value"},
 *          {"name"="custom_category", "dataType"="string[]", "description"="an array of exact custom categories names"},
 *          {"name"="status", "dataType"="integer", "description"="an integer value represents current status"},
 *          {"name"="hidden_status", "dataType"="string", "description"="an integer value represents current hidden_status"},
 *          {"name"="created_from", "dataType"="datetime", "description"="a datetime string to search comments since"},
 *          {"name"="created_to", "dataType"="datetime", "description"="a datetime string to search comments until"},
 *          {
 *              "name"="feedback_field.{id}",
 *              "description"="
 *                  Custom feedback field filter. To filter by a custom field with ID=1 you need to add
 *                  ?feedback_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *      }
 * )
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\FeedbackCommentType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\FeedbackComment"
 *      }
 *     }
 * )
 */
class FeedbackAllCommentsController extends AbstractFeedbackController
{
    public static $exposeOnly  = ['get', 'list', 'count', 'put', 'delete'];
    public static $entity      = FeedbackComment::class;
    public static $type        = FeedbackCommentType::class;
    public static $listOrder   = 'asc';
    public static $sortOptions = [
        'date_created' => 'date_created',
        'total_rating' => 'feedback.total_rating',
    ];

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\FeedbackComment": "DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\FeedbackCommentCsv"
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
        $qb->join("$alias.feedback", 'feedback');

        $this->applyNotReviewedFilters($qb, $alias, $request);
        $this->applyDateCreatedFilters($qb, $alias, $request);
        $this->applyFeedbackListFilters($qb, 'feedback', $request);

        ListHelper::applyInListFilter(new RequestQueryContext($qb, 'feedback', $request), 'id', 'feedback_ids');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'feedback') {
            $qb
                ->addSelect('feedback.title as title')
                ->addSelect('feedback.id as group_name')
                ->groupBy('group_name')
            ;
        }
    }
}
