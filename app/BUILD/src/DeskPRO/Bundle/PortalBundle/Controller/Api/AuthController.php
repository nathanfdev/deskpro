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
        $brand                  = $this->get('brand_stack')->getActive()->getBrand();

        // track visitor id
        $visitorId = $this->get('visitor_identification_provider')->getVisitorIdentifier(true);
        $request->attributes->set(VisitorIdentificationProvider::ATTRIBUTE_NAME, $visitorId);

        $lastChat     = $this->getLastChat();
        $trackVisitor = $request->request->get('trackVisitor');

        if ($trackVisitor) {
            $urlSettings = $widgetSettingsResolver->getWidgetUrlSettings($brand, $request);

            try {
                $hit = $this->get('hitrecord.record_factory')->fromParameters($trackVisitor, $request, $visitorId);

                // write hit record if it's from an external site
                // otherwise we have PageHitController to track visitor
                if (strpos($hit->getUrl(), $urlSettings->getHelpdesk()) === false) {
                    $this->get('hitrecord.record_storage')->record($hit);
                }
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
            $widgetSettingsResolver->getWidgetGlobalOptions($brand),
            $this->isGranted(UseSectionVoter::USE_CHAT),
            $this->container->get('language_stack')->getActiveOrDefault(),
            $lastChat ? $lastChat->getAuthId() : null,
            $this->get('deskpro.app_env')->getBuildId()
        );

        return new View($this->wrap($model));
    }
}
