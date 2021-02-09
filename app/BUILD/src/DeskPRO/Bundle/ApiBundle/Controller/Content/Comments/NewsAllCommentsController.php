<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Comments;

use Application\DeskPRO\Entity\NewsComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Form\RatingType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Comment\NewsCommentType;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NewsAllCommentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/news_comments")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\NewsComment")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="status", "dataType"="string", "pattern"="visible|deleted|agent|validating", "description"="filter by status"},
 *          {"name"="is_reviewed", "dataType"="integer", "pattern"="1|0", "description"="filter by reviewed status"},
 *          {"name"="period_created", "dataType"="string", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period"},
 *          {"name"="article", "dataType"="integer", "pattern"="\d+", "description"="select comments for article with given id"},
 *          {"name"="download", "dataType"="integer", "pattern"="\d+", "description"="select comments for download with given id"},
 *          {"name"="news", "dataType"="integer", "pattern"="\d+", "description"="select comments for news with given id"}
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *          {"name"="order_by", "dataType"="string", "pattern"="date_created|person", "description"="how to order comments"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *          {"name"="group_by", "dataType"="string", "pattern"="news|status|period_created", "description"="how to groups comments"}
 *     }
 * )
 * @RequireAgentPermissions()
 */
class NewsAllCommentsController extends AbstractAllCommentsController
{
    public static $contentType = 'news';
    public static $entity      = NewsComment::class;
    public static $type        = NewsCommentType::class;
    public static $exposeOnly  = ['put', 'post'];


    /**
     * Rate news comment.
     *
     * @ApiDoc(
     *     section="News",
     *     description="rate news comment",
     *     requirements={
     *          {
     *              "name"="news",
     *              "requirement"="\d+",
     *              "description"="the id of news comment",
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
     * @param NewsComment $comment
     * @param null $visitor_id
     * @return View
     */
    public function rateNewsContentAction(Request $request, NewsComment $comment, $visitor_id = null)
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
     * @param NewsComment $comment
     * @return View|NotFoundHttpException
     */
    public function getNewsCommentCountAction(NewsComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }

}
