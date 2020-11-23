<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\ApiBundle\Form\RatingType;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RateController.
 *
 * @Rest\Route("/portal/api")
 */
class RateController extends AbstractApiController
{

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
     * @Rest\Post("/news_comments/{comment}/rate")
     *
     * @param Request $request
     * @param NewsComment $comment
     * @param null $visitor_id
     * @return View
     */
    public function rateNewsContentAction(Request $request, NewsComment $comment, $visitor_id = null)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        $form = $this->createFormAction();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $this->get('rating_service')->rateContent($comment->getRatingData(),
                $form->get('upvote')->getData(), $person, $visitor_id);
        }

        return new View($this->wrap($comment));
    }

    /**
     * @Rest\Get("/news_comments/{comment}/rate_count")
     *
     * @param NewsComment $comment
     * @return View|NotFoundHttpException
     */
    public function getNewsCommentCount(NewsComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }

    /**
     * Rate download comment.
     *
     * @ApiDoc(
     *     section="Downloads",
     *     description="rate download comment",
     *     requirements={
     *          {
     *              "name"="downloads",
     *              "requirement"="\d+",
     *              "description"="the id of download",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="DeskPRO\Bundle\ApiBundle\Form\RatingType",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/download_comments/{download}/rate")
     *
     * @param Request $request
     * @param DownloadComment $download
     * @param null $visitor_id
     * @return View
     */
    public function rateDownloadContentAction(Request $request, DownloadComment $download, $visitor_id = null)
    {
        $form = $this->createFormAction();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $this->get('rating_service')->rateContent($download->getRatingData(),
                $form->get('upvote')->getData(), $this->getUser(), $visitor_id);
        }

        return new View($this->wrap($download));
    }

    /**
     * @Rest\Get("/download_comments/{download}/rate_count")
     *
     * @param DownloadComment $download
     * @return View|NotFoundHttpException
     */
    public function getDownloadCommentCount(DownloadComment $download)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($download->getRatingData())));
    }

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
     * @Rest\Post("/article_comments/{article}/rate")
     *
     * @param Request $request
     * @param ArticleComment $article
     * @param null $visitor_id
     * @return View
     * @throws Exception
     */
    public function rateArticleCommentAction(Request $request, ArticleComment $article, $visitor_id = null)
    {
        $form = $this->createFormAction();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $this->get('rating_service')->rateContent($article->getRatingData(),
                $form->get('upvote')->getData(), $this->getUser(), $visitor_id);
        }

        return new View($this->wrap($article));
    }

    /**
     * @Rest\Get("/article_comments/{article}/rate_count")
     *
     * @param ArticleComment $article
     * @return View|NotFoundHttpException
     */
    public function getArticleCommentCount(ArticleComment $article)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($article->getRatingData())));
    }

    /**
     * Rate article comment.
     *
     * @ApiDoc(
     *     section="Community",
     *     description="rate community topic comment",
     *     requirements={
     *          {
     *              "name"="community",
     *              "requirement"="\d+",
     *              "description"="the id of community topic comment",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="DeskPRO\Bundle\ApiBundle\Form\RatingType",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/community_topic_comments/{comment}/rate")
     *
     * @param Request $request
     * @param CommunityTopicComment $comment
     * @param null $visitor_id
     * @return View
     */
    public function rateCommunityTopicCommentAction(
        Request $request,
        CommunityTopicComment $comment,
        $visitor_id = null
    ) {
        $form = $this->createFormAction();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $this->get('rating_service')->rateContent($comment->getRatingData(),
                $form->get('upvote')->getData(), $this->getUser(), $visitor_id);
        }

        return new View($this->wrap($comment));
    }

    /**
     * @Rest\Get("/community_topic_comments/{comment}/rate_count")
     *
     * @param CommunityTopicComment $comment
     * @return View|NotFoundHttpException
     */
    public function getCommunityTopicCommentCount(CommunityTopicComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }

    /**
     * Rate guide comment.
     *
     * @ApiDoc(
     *     section="Guides",
     *     description="rate guide topic comment",
     *     requirements={
     *          {
     *              "name"="guide",
     *              "requirement"="\d+",
     *              "description"="the id of guide comment",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="DeskPRO\Bundle\ApiBundle\Form\RatingType",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/guide_comments/{comment}/rate")
     *
     * @param Request $request
     * @param TopicComment $comment
     * @param null $visitor_id
     * @return View
     */
    public function rateGuideCommentAction(
        Request $request,
        TopicComment $comment,
        $visitor_id = null
    ) {
        $form = $this->createFormAction();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $this->get('rating_service')->rateContent($comment->getRatingData(),
                $form->get('upvote')->getData(), $this->getUser(), $visitor_id);
        }

        return new View($this->wrap($comment));
    }

    /**
     * @Rest\Get("/guide_comments/{comment}/rate_count")
     *
     * @param TopicComment $comment
     * @return View|NotFoundHttpException
     */
    public function getGuideCommentCount(TopicComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }

    public function createFormAction()
    {
        return $this->createForm(RatingType::class, null, []);
    }
}
