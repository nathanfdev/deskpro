<?php

namespace DeskPRO\Bundle\AppBundle\Security\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\SessData;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\EntityRepository\ChatConversation as ChatConversationRepository;
use Application\DeskPRO\EntityRepository\Session as SessionRepository;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\TaskRouter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
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
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TaskRouter
     */
    private $taskRouter;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param HttpUtils                $httpUtils
     * @param UrlGeneratorInterface    $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param TaskRouter               $taskRouter
     * @param TokenStorage             $tokenStorage
     */
    public function __construct(
        EntityManager $em,
        HttpUtils $httpUtils,
        UrlGeneratorInterface $router,
        EventDispatcherInterface $eventDispatcher,
        TaskRouter $taskRouter,
        TokenStorage $tokenStorage
    ) {
        $this->em                  = $em;
        $this->httpUtils           = $httpUtils;
        $this->router              = $router;
        $this->eventDispatcher     = $eventDispatcher;
        $this->taskRouter          = $taskRouter;
        $this->tokenStorage        = $tokenStorage;
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
        foreach (['dpsid-portal', 'dpsid-agent', 'dpsid-admin', 'dpreme'] as $cookie_name) {
            if (!empty($_COOKIE[$cookie_name])) {
                if ($cookie_name === 'dpsid-portal') {
                    $sess2 = $this->em->getRepository(SessData::class)->findOneBy(['sess_id' => $_COOKIE[$cookie_name]]);
                } else {
                    $sess2 = $this->em->getRepository(Session::class)->getSessionFromCode($_COOKIE[$cookie_name]);
                }

                if ($sess2) {
                    $this->endUserChatBySession($sess2);
                    $this->em->remove($sess2);
                    $this->em->flush();
                }
            }

            $cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie($cookie_name);
            $cookie->send();
        }
        $this->endUserChatByUser();
        $this->endUserChatByVisitorId($visitorId = $request->cookies->get(VisitorIdentificationProvider::COOKIE_NAME));

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
                return new RedirectResponse($this->router->generate('portal_home'));
        }
    }

    /**
     * @param Session $session
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function endUserChatBySession(Session $session = null)
    {
        if(!$session){
            return;
        }
        
        /** @var ChatConversationRepository $chatRepo */
        $chatRepo = $this->em->getRepository(ChatConversation::class);
        if ($conversation = $chatRepo->getLatestChatForSession($session->getId())) {
            $this->endChat($conversation);
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function endUserChatByUser()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user->getId()) {
            /** @var Person $user */
            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $this->em->getRepository(Session::class);
            $session           = $sessionRepository->getSessionForPerson($user);
            $this->endUserChatBySession($session);
        }
    }

    /**
     * @param string $visitorId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function endUserChatByVisitorId($visitorId)
    {
        /** @var ChatConversationRepository $chatRepo */
        $chatRepo     = $this->em->getRepository(ChatConversation::class);
        if ($conversation = $chatRepo->getActiveChatForVisitor($visitorId)) {
            $this->endChat($conversation);
        }
    }

    /**
     * @param $conversation
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function endChat($conversation)
    {
        $conversation
            ->setStatus(ChatConversation::STATUS_ENDED)
            ->setEndedBy('user');

        $this->em->persist($conversation);
        $this->em->flush();

        $this->eventDispatcher->dispatch(UserChatEvent::END_BY_USER, new UserChatEvent($conversation, [], ['chat_ended']));
        $this->taskRouter->endTask($conversation->getTaskId());
    }
}
