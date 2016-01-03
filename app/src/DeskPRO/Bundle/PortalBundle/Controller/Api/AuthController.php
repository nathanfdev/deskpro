<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Session;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController.
 */
class AuthController extends AbstractApiController
{
    /**
     * @Route("/portal/api/auth/get_session", name="portal_api_auth_get_session")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function getSessionAction(Request $request)
    {
        /** @var \Application\DeskPRO\EntityRepository\Session $session_repository */
        $session_repository = $this->getDoctrine()->getRepository('DeskPRO:Session');

        $session_code = $request->request->get('session_code');
        $session      = $session_repository->getSessionFromCode($session_code);

        if (!$session) {
            $session = new Session();

            $em = $this->getDoctrine()->getManager();
            $em->persist($session);
            $em->flush();
        }

        return new View([
            'session_code' => $session->getSessionCode(),
        ]);
    }
}
