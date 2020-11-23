<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Comments;

use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\NewsComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Form\RatingType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Comment\DownloadCommentType;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DownloadsAllCommentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/download_comments")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\DownloadComment")
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
 *          {"name"="group_by", "dataType"="string", "pattern"="download|status|period_created", "description"="how to groups comments"}
 *     }
 * )
 * @RequireAgentPermissions()
 */
class DownloadsAllCommentsController extends AbstractAllCommentsController
{
    public static $contentType = 'download';
    public static $entity      = DownloadComment::class;
    public static $type        = DownloadCommentType::class;
    public static $exposeOnly  = ['put', 'post'];

    /**
     * Rate download.
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
     * @Rest\Post("/{download}/rate")
     *
     * @param Request $request
     * @param DownloadComment $download
     * @param null $visitor_id
     * @return View
     */
    public function rateDownloadContentAction(Request $request, DownloadComment $download, $visitor_id = null)
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
            $rating = $this->get('rating_service')->rateContent($download->getRatingData(), $form->get('upvote')->getData(), $person, $visitor_id);
        }

        return new View($this->wrap($download));
    }

    /**
     * @Rest\Get("/{comment}/rate_count")
     *
     * @param DownloadComment $comment
     * @return View|NotFoundHttpException
     */
    public function getDownloadCommentCountAction(DownloadComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }
}
