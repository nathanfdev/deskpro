<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Comments;

use Application\DeskPRO\Entity\ArticleComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Form\RatingType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Comment\ArticleCommentType;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ArticleAllCommentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/article_comments")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\ArticleComment")
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
 *          {"name"="group_by", "dataType"="string", "pattern"="article|status|period_created", "description"="how to groups comments"}
 *     }
 * )
 * @RequireAgentPermissions()
 */
class ArticleAllCommentsController extends AbstractAllCommentsController
{
    public static $contentType = 'article';
    public static $entity      = ArticleComment::class;
    public static $type        = ArticleCommentType::class;
    public static $exposeOnly  = ['put', 'post'];

    /**
     * Rate article comment.
     *
     * @ApiDoc(
     *     section="Articles",
     *     description="rate article content",
     *     requirements={
     *          {
     *              "name"="articles",
     *              "requirement"="\d+",
     *              "description"="the id of article",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="DeskPRO\Bundle\ApiBundle\Form\RatingType",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/{article}/rate")
     *
     * @param Request $request
     * @param ArticleComment $article
     * @param null $visitor_id
     * @return View
     * @throws Exception
     */
    public function rateArticleCommentAction(Request $request, ArticleComment $article, $visitor_id = null)
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
            $rating = $this->get('rating_service')->rateContent($article->getRatingData(), $form->get('upvote')->getData(), $person, $visitor_id);
        }

        return new View($this->wrap($article));
    }

    /**
     * @Rest\Get("/{comment}/rate_count")
     *
     * @param ArticleComment $comment
     * @return View|NotFoundHttpException
     */
    public function getArticleCommentCountAction(ArticleComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }
}
