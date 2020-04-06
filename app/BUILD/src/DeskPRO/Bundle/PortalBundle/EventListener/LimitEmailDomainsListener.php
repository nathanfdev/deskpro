<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\AppBundle\Security\LimitEmailDomainsChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class LimitEmailDomainsListener.
 */
class LimitEmailDomainsListener implements EventSubscriberInterface
{
    /**
     * @var LimitEmailDomainsChecker
     */
    private $domainsChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * Constructor.
     *
     * @param LimitEmailDomainsChecker $domainsChecker
     * @param TokenStorageInterface    $tokenStorage
     * @param HttpUtils                $httpUtils
     */
    public function __construct(LimitEmailDomainsChecker $domainsChecker, TokenStorageInterface $tokenStorage, HttpUtils $httpUtils)
    {
        $this->domainsChecker = $domainsChecker;
        $this->tokenStorage   = $tokenStorage;
        $this->httpUtils      = $httpUtils;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 0],
        ];
    }

    /**
     * @inheritDoc
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()
            || RequestUtils::isLowRequest($request)
            || RequestUtils::isPortalApi($request)
        ) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof Person) {
            return;
        }

        if (!$this->domainsChecker->checkPerson($user)) {
            $this->tokenStorage->setToken(null);
            $event->setResponse($this->httpUtils->createRedirectResponse($request, 'portal_login'));
        }
    }
}
