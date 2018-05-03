<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Controller\ExceptionController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class ApiEndpointListener.
 */
class ApiEndpointListener implements EventSubscriberInterface
{
    /**
     * @param AuthorizationChecker $checker
     */
    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 1024],
        ];
    }

    /**
     * @var AuthorizationChecker
     */
    protected $checker;

    /**
     * @param FilterControllerEvent $event
     */
    public function onController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())
            || !$controller[0] instanceof BaseController
            || $controller[0] instanceof ExceptionController) {
            return;
        }

        if (!$this->checker->isGranted($controller[1], $controller[0])) {
            throw new AccessDeniedHttpException('You are not allowed to access this point with this auth mode', null, 403);
        }
    }
}
