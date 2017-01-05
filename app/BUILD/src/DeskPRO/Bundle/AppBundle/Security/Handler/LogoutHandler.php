<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Security\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * When a logout request is made, this class is notified so it can do some cleanup.
 */
class LogoutHandler implements LogoutHandlerInterface, LogoutSuccessHandlerInterface
{
    const RECENT_LOGOUT = 'recent_logout';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    public function __construct(EntityManager $em, HttpUtils $httpUtils)
    {
        $this->em        = $em;
        $this->httpUtils = $httpUtils;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if ($request->get('_dp_impersonate_exit')) {
            $request->getSession()->remove('is_impersonating');

            return; // pass this param to avoid a full logout (only log out of portal)
        }

        // duplicated for now, from UserBundle:Login:logoutAction
        foreach (['dpsid-agent', 'dpsid-admin', 'dpreme'] as $cookie_name) {
            if (!empty($_COOKIE[$cookie_name])) {
                $sess2 = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($_COOKIE[$cookie_name]);
                if ($sess2) {
                    $this->em->remove($sess2);
                    $this->em->flush();
                }
            }

            $cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie($cookie_name);
            $cookie->send();
        }

        $request->getSession()->set(self::RECENT_LOGOUT, time());
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        switch ($request->get('to', null)) {
            case 'admin':
                return $this->httpUtils->createRedirectResponse($request, '/admin/');
            case 'agent':
                return $this->httpUtils->createRedirectResponse($request, '/agent/');
            default:
                return $this->httpUtils->createRedirectResponse($request, '/');
        }
    }
}
