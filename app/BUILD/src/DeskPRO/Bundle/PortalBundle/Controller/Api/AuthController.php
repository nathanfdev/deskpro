<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\UseSectionVoter;
use DeskPRO\Bundle\PortalBundle\Model\WidgetSession;
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
        /** @var \Application\DeskPRO\EntityRepository\Session $repository */
        $repository = $this->getDoctrine()->getRepository(Session::class);

        // try to get session from widget dpsid
        $session = $repository->getSessionFromCode($request->request->get('dpsid'));

        // try to get session from portal session
        if (!$session && $request->getSession() && $request->getSession()->getId()) {
            $authCode = substr($request->getSession()->getId(), 0, 15);
            $session  = $repository->findOneBy([
                'auth' => $authCode,
            ]);
        }

        $changed = false;
        if (!$session) {
            // create a new session
            $session = new Session();
            if ($request->getSession() && $request->getSession()->getId()) {
                $session->setAuth($request->getSession()->getId());
            }

            $changed = true;
        }
        if (!$session->getPerson() && $this->getUser()) {
            $session->setPerson($this->getUser());
            $changed = true;
        }

        if ($changed) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($session);
            $em->flush();
        }

        $this->get('dpsid.listener')->setPortalApiToken($session, $request);

        return new View($this->wrap(new WidgetSession(
            $session,
            $this->container->get('widget_settings_resolver')->getWidgetGlobalOptions(),
            $this->isGranted(UseSectionVoter::USE_CHAT),
            $this->container->get('language_stack')->getActiveOrDefault(),
            $this->getLastChatId()
        )));
    }

    /**
     * @return int|null
     */
    protected function getLastChatId()
    {
        $storedChatId = $this->getWidgetOption('chat_id');
        if ($storedChatId) {
            $conversation = $this->getManager()->getRepository(ChatConversation::class)->find($storedChatId);
            if ($conversation && !$conversation->getDateEnded()) {
                return $conversation->getId();
            }
        }

        return;
    }
}
