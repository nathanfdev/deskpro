<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Comments;

use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Form\RatingType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Comment\GuidesCommentType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GuidesAllCommentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/guides_comments")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\TopicComment")
 * )
 * @RequireAgentPermissions()
 */
class GuidesAllCommentsController extends AbstractAllCommentsController
{
    public static $entity      = TopicComment::class;
    public static $type        = GuidesCommentType::class;
    public static $exposeOnly  = ['put', 'post'];
    public static $contentType = 'guides';

    /**
     * Rate guide comment.
     *
     * @ApiDoc(
     *     section="Guide",
     *     description="rate guide comment",
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
     * @Rest\Post("/{comment}/rate")
     *
     * @param Request $request
     * @param TopicComment $comment
     * @param null $visitor_id
     *
     * @return View
     */
    public function rateGuideContentAction(Request $request, TopicComment $comment, $visitor_id = null)
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
     * @param TopicComment $comment
     *
     * @return View|NotFoundHttpException
     */
    public function getGuideCommentCountAction(TopicComment $comment)
    {
        return new View($this->wrap($this->get('rating_service')->ratingCount($comment->getRatingData())));
    }
}
