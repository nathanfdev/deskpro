<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\Notifications\NewCommentNotification;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\View\View;
use Orb\Util\Arrays;
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
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\Person": "DeskPRO\Bundle\AppBundle\Serializer\Model\Person\WidgetPerson"
 * })
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

        if ($this->isHelpCenterTheme()) {
            $topics = array_values(Arrays::flattenHierarchy($topics));
        }

        return new View($this->wrap($topics), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/all/{slug}", name="portal_api_guides_topics_all")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Method({"GET"})
     *
     * @param Guide $guide
     *
     * @return View|NotFoundHttpException
     */
    public function getGuideAllTopics(Guide $guide)
    {
        $person = $this->getUser();
        $topics = $this->get('data.guides')->getGuideTopics($guide, $person);

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
        $translate   = $this->container->get('deskpro.core.translate');
        $language    = $this->container->get('language_stack')->getActiveOrDefault();
        $comment     = new TopicComment();
        $comment->setVisitorId($visitor_id);
        $comment->setIpAddress($request->getClientIp());
        $newCommentForm = $formHandler->createForm($comment, $request);
        $formResult     = $formHandler->handle($newCommentForm, $request, $topic, $comment);

        // Workaround for Guest comments email validation
        // If processing comment in sub-request - then we got here from email validation link (route: `portal_validation`)
        // in this case we need to return regular page response instead of json response
        if (
            $formResult instanceof Response
            && $this->get('request_stack')->getParentRequest()
            && $request->attributes->get('saved-form')
        ) {
            return $formResult;
        }

        $flashes = [];
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
            foreach ($response['errors']->getForm()->getErrors() as $error) {
                $response['general_errors'][] = $translate->getPhraseText('portal.forms.error_'.$error->getMessage());
            }
        } else {
            $notify = new NewCommentNotification($comment);
            $notify->send();
        }

        return new View($this->wrap($response), Response::HTTP_OK);
    }

    /**
     * @Route("/portal/api/guides/topic_children/{slug}", name="portal_api_guides_topic_children")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Method({"GET"})
     *
     * @param Topic $topic
     *
     * @return View|NotFoundHttpException
     */
    public function getTopicChildrenAction(Topic $topic)
    {
        return new View($this->wrap($topic->getChildren()), Response::HTTP_OK);
    }
}
