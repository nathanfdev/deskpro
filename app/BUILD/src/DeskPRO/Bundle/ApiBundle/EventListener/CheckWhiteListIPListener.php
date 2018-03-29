<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Service\CheckWhitelistedIP;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class CheckWhiteListIPListener.
 */
class CheckWhiteListIPListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface|DeskproContainer
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        // lazy loading of required services
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $token   = $this->container->get('security.token_storage')->getToken();
        $person  = $token->getUser();

        if (!$person instanceof Person) {
            return;
        }

        if (!CheckWhitelistedIP::checkIP($request, $this->container, $person)) {
            throw new AccessDeniedHttpException(ErrorsCodes::IP_VERIFY, null, Response::HTTP_FORBIDDEN);
        }
    }
}
