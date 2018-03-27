<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\UseSectionVoter;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatMessages;
use DeskPRO\Bundle\PortalBundle\Model\WidgetSession;
use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController.
 *
 * @Rest\Route("/portal/api/auth")
 */
class AuthController extends AbstractApiController
{
    /**
     * @ApiDoc(
     *     section="Portal widget",
     *     description="Widget session",
     *     statusCodes={
     *         200="Success"
     *     },
     *     output="DeskPRO\Bundle\PortalBundle\Model\WidgetSession"
     *)
     *
     * @Rest\Post("/session")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getSessionAction(Request $request)
    {
        $widgetSettingsResolver = $this->get('widget_settings_resolver');

        // track visitor id
        $visitorId = $this->get('visitor_identification_provider')->getVisitorIdentifier(true);
        $request->attributes->set(VisitorIdentificationProvider::ATTRIBUTE_NAME, $visitorId);

        $lastChat     = $this->getLastChat();
        $trackVisitor = $request->request->get('trackVisitor');

        if ($trackVisitor) {
            try {
                $hit = $this->get('hitrecord.record_factory')->fromParameters($trackVisitor, $request, $visitorId);
                $this->get('hitrecord.record_storage')->record($hit);
            } catch (\Exception $e) {
                $hit = null;
            }

            if ($lastChat) {
                if ($lastChat->getVisitorId() !== $visitorId) {
                    $lastChat->setVisitorId($visitorId);
                }
                if ($hit && $hit->getUrl()) {
                    $trackMsg = UserChatMessages::createUserTrackMessage($lastChat, $hit->getUrl());
                    $lastChat->addMessage($trackMsg);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trackMsg);
                    $em->persist($lastChat);
                    $em->flush();

                    $this->dispatch(UserChatEvent::USER_TRACK, new UserChatEvent($lastChat, $trackMsg));
                }
            }
        }

        $model = new WidgetSession(
            $this->get('security.token_storage')->getToken(),
            $widgetSettingsResolver->getWidgetGlobalOptions(),
            $this->isGranted(UseSectionVoter::USE_CHAT),
            $this->container->get('language_stack')->getActiveOrDefault(),
            $lastChat ? $lastChat->getAuthId() : null,
            $this->get('deskpro.app_env')->getVersionName()
        );

        return new View($this->wrap($model));
    }
}
