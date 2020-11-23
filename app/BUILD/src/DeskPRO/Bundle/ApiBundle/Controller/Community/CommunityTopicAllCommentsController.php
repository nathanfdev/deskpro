<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityTopicComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Form\RatingType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Comment\CommunityTopicCommentType;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Model\RatingModel;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API access to community topic comments.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_topic_comments")
 * @ApiDoc(target="all", section="Community", output="Application\DeskPRO\Entity\CommunityTopicComment")
 * @ApiDoc(
 *      target="listAction",
 *      tags={"community"="#4422bb", "comments"="#22aa22"},
 *      description="get list of community topic comments",
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
 *              "name"="community_topic_field.{id}",
 *              "description"="
 *                  Custom community topic field filter. To filter by a custom field with ID=1 you need to add
 *                  ?community_topic_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *      }
 * )
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CommunityTopicCommentType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CommunityTopicComment"
 *      }
 *     }
 * )
 */
class CommunityTopicAllCommentsController extends AbstractCommunityController
{
    public static $exposeOnly  = ['get', 'list', 'count', 'put', 'post', 'delete'];
    public static $entity      = CommunityTopicComment::class;
    public static $type        = CommunityTopicCommentType::class;
    public static $listOrder   = 'asc';
    public static $sortOptions = [
        'date_created' => 'date_created',
        'total_rating' => 'topic.total_rating',
    ];

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\CommunityTopicComment": "DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommunityTopicCommentCsv"
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
     * Rate article.
     *
     * @ApiDoc(
     *     section="Topics",
     *     description="rate topic comment",
     *     requirements={
     *          {
     *              "name"="topics",
     *              "requirement"="\d+",
     *              "description"="the id of comment",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="DeskPRO\Bundle\ApiBundle\Form\RatingType",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/{comment}/rate")
     *
     * @param Request $request
     * @param CommunityTopicComment $comment
     * @param null $visitor_id
     * @return View
     */
    public function rateDownloadContentAction(Request $request, CommunityTopicComment $comment, $visitor_id = null)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        $form = $this->createForm(RatingType::class, null, [
            'allow_extra_fields' => false,
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $this->get('rating_service')->rateContent($comment->getRatingData(), $form->get('upvote')->getData(), $person, $visitor_id);
        }

        return new View($this->wrap($comment));
    }

    /**
     * @Rest\Get("/{comment}/rate_count")
     *
     * @param CommunityTopicComment $comment
     * @return View|NotFoundHttpException
     */
    public function getCommunityTopicCommentCountAction(CommunityTopicComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->join("$alias.topic", 'topic');

        $this->applyNotReviewedFilters($qb, $alias, $request);
        $this->applyDateCreatedFilters($qb, $alias, $request);
        $this->applyCommunityListFilters($qb, 'topic', $request);

        ListHelper::applyInListFilter(new RequestQueryContext($qb, 'topic', $request), 'id', 'topic_ids');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'topic') {
            $qb
                ->addSelect('topic.title as title')
                ->addSelect('topic.id as group_name')
                ->groupBy('group_name')
            ;
        }
    }
}
