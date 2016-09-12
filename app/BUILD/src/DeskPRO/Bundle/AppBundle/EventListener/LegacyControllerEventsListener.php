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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\HttpKernel\Event\PrePostEvent;
use DeskPRO\Component\Collections\LazyArrayObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Old controllers make use of controller methods that run pre/post controller.
 * This event listener makes sure to call those methods on those old controllers.
 */
class LegacyControllerEventsListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var mixed
     */
    private $lastController;

    /**
     * @var LazyArrayObject|null
     */
    private $lastControllerArgs;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 100],
            KernelEvents::RESPONSE   => ['onResponse', 100],
        ];
    }

    /**
     * Before a controller is called, we call the controllers pre method.
     * If the pre method returns a response, then we use that as the request
     * response (the real action is not called).
     *
     * @param FilterControllerEvent $event
     */
    public function onController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();

        if (!$this->isLegacyController($controller)) {
            return;
        }

        $this->lastController = $controller;

        $container = $this->container;
        $request   = $event->getRequest();

        $arguments = new LazyArrayObject(function () use ($container, $request, $controller) {
            $resolver = $container->get('controller_resolver');

            return $resolver->getArguments($request, $controller);
        });

        $this->lastControllerArgs = $arguments;

        // Run pre event
        $legacyEvent = new PrePostEvent([
            'request_type' => $event->getRequestType(),
            'request'      => $request,
            'controller'   => $controller[0],
            'action'       => $controller[1],
            'arguments'    => $arguments,
        ]);
        $controller[0]->DeskPRO_onControllerPreActionHandler($legacyEvent);

        if ($legacyEvent->hasResponse()) {
            // override the real controller with the response from
            // legacy event handler
            $event->setController(function () use ($legacyEvent) {
                return $legacyEvent->getResponse();
            });
        }
    }

    /**
     * After a controller is called, we call the controllers post method.
     * If the post method returns a response, then we use that as the response
     * (and ignore the real response the controller might have generated).
     *
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $this->lastController;
        $arguments  = $this->lastControllerArgs;

        $this->lastController     = null;
        $this->lastControllerArgs = null;

        if (!$this->isLegacyController($controller)) {
            return;
        }

        // Run pre event
        $legacyEvent = new PrePostEvent([
            'request_type' => $event->getRequestType(),
            'request'      => $event->getRequest(),
            'controller'   => $controller[0],
            'action'       => $controller[1],
            'arguments'    => $arguments,
            'response'     => $event->getResponse(),
        ]);
        $controller[0]->DeskPRO_onControllerPostActionHandler($legacyEvent);

        if ($legacyEvent->hasResponse()) {
            // ignore the real response and override it with the event
            $event->setResponse($legacyEvent->getResponse());
        }
    }

    /**
     * @param mixed $controller
     *
     * @return bool
     */
    private function isLegacyController($controller)
    {
        return $controller
            && is_array($controller)
            && isset($controller[0])
            && is_object($controller[0])
            && method_exists($controller[0], 'DeskPRO_onControllerPreActionHandler');
    }
}
