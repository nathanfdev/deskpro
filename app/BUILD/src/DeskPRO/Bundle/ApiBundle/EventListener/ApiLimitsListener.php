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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Controller\ExceptionController;
use DeskPRO\Bundle\ApiBundle\Limits\Exception\LimitExhaustedException;
use DeskPRO\Bundle\ApiBundle\Limits\LimitsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiLimitsListener implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container; // we really do not want to get LimitsService as soon as possible, but only when it would be needed
    }

    public static function getSubscribedEvents()
    {
        return [
          KernelEvents::CONTROLLER => ['onController', 512],
        ];
    }

    public function onController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if ($controller[0] instanceof BaseController && !$controller[0] instanceof ExceptionController) {
            /** @var LimitsService $service */
            $service = $this->container->get('api_limits.limits_service');
            try {
                $service->checkLimits($controller[0], $controller[1]);
            } catch (LimitExhaustedException $e) {
                throw new AccessDeniedHttpException($e->getMessage(), $e);
            }

            $service->reduceLimits();
        }
    }
}
