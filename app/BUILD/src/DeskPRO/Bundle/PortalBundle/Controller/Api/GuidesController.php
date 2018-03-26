<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\Notifications\NewCommentNotification;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GuidesController.
 *
 * @Feature("guides")
 */
class GuidesController extends AbstractApiController
{
    /**
     * @Route("/portal/api/guides/topic/{slug}", name="portal_api_guides_topic")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Method({"GET"})
     *
     * @param Topic $topic
     *
     * @return View|NotFoundHttpException
     */
    public function getTopicAction(Topic $topic)
    {
        return new View($this->wrap($topic), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/guide/{slug}", name="portal_api_guides_guide")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Method({"GET"})
     *
     * @param Guide $guide
     *
     * @return View|NotFoundHttpException
     */
    public function getGuideAction(Guide $guide)
    {
        return new View($this->wrap($guide), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/topics/{slug}", name="portal_api_guides_topics")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Method({"GET"})
     *
     * @param Guide $guide
     *
     * @return View|NotFoundHttpException
     */
    public function getGuideTopicsAction(Guide $guide)
    {
        $person = $this->getUser();
        $topics = $this->get('data.guides')->getGuideChildren(
            $guide,
            $person
        );

        return new View($this->wrap($topics), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/topic/{slug}/comment", name="portal_api_guides_topic_comment")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Topic   $topic
     * @param $visitor_id
     *
     * @return View|NotFoundHttpException|AccessDeniedException
     */
    public function postNewComment(Request $request, Topic $topic, $visitor_id)
    {
        if (!$this->isGranted(ContentCommentVoter::COMMENT_TOPIC, $topic)) {
            return $this->createAccessDeniedException();
        }

        $formHandler = $this->get('form_handler.comment');
        $comment     = new TopicComment();
        $comment->setVisitorId($visitor_id);
        $comment->setIpAddress($request->getClientIp());
        $newCommentForm = $formHandler->createForm($comment, $request);
        $formResult     = $formHandler->handle($newCommentForm, $request, $topic, $comment);
        $flashes        = [];
        foreach ($request->getSession()->getFlashBag()->all() as $type => $flash) {
            $flashes[] = [
                'type'    => $type,
                'message' => $flash[0],
            ];
        }
        $response = ['flashes' => $flashes];
        if ($comment->getId() && $comment->getStatus() === CommentAbstract::STATUS_VISIBLE) {
            $response['comment'] = $comment;
        }
        if (!$formResult && $errors = $newCommentForm->getErrors(true)) {
            $response['errors'] = $errors;
        } else {
            $notify = new NewCommentNotification($comment);
            $notify->send();
        }

        return new View($this->wrap($response), Response::HTTP_OK);
    }
}
