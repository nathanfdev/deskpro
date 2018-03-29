<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\LogoutException;

/**
 * Class LogoutExceptionRedirectListener.
 */
class LogoutExceptionRedirectListener
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $redirectActionName;

    /**
     * @param RouterInterface $router
     * @param string          $redirectActionName
     */
    public function __construct(RouterInterface $router, $redirectActionName)
    {
        $this->router             = $router;
        $this->redirectActionName = $redirectActionName;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @return RedirectResponse
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof LogoutException && $exception->getMessage() === 'Invalid CSRF token.') {
            $url = $this->router->generate($this->redirectActionName);
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
