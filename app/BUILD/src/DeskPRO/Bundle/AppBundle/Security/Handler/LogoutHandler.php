<?php

namespace DeskPRO\Bundle\AppBundle\Security\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Cookie;
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

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param HttpUtils     $httpUtils
     */
    public function __construct(EntityManager $em, HttpUtils $httpUtils)
    {
        $this->em        = $em;
        $this->httpUtils = $httpUtils;
    }

    /**
     * {@inheritdoc}
     */
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

        // we are using this callback for legacy agent logout as well
        // so create a cookie for automatic sso to prevent logging in back on redirect
        $response->headers->setCookie(new Cookie('dp-recent-logout', true, new \DateTime('+5 minutes')));
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
