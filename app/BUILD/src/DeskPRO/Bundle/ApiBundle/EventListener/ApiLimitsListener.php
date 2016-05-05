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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Controller\ExceptionController;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\AppBundle\Annotation\Limits\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Limits\Exception\LimitExhaustedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiLimitsListener.
 */
class ApiLimitsListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 512],
        ];
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $token      = $this->container->get('security.token_storage')->getToken();

        if (!$token
            || !$token instanceof ApiKeySecurityToken
            || !$controller[0] instanceof BaseController
            || $controller[0] instanceof ExceptionController
        ) {
            // so we have no token or it's not key auth
            // or we have not BaseController descendant or it's ExceptionController which we don't serve
            return;
        }

        $class  = get_class($controller[0]);
        $action = $controller[1];

        $classMetadata = $this->container->get('api_limits.metadata_factory')->getMetadataForClass($class);
        if ($classMetadata) {
            /** @var MethodMetadata[] $methodMetadata */
            $methodMetadata = $classMetadata->methodMetadata;
            if (isset($methodMetadata[$action]) && $methodMetadata[$action]->isLimitsDisabled()) {
                return;
            }
        }

        try {
            $limits = $this->container->get('api_limits.limits_service');
            $limits->checkLimits();
            $limits->reduceLimits();
        } catch (LimitExhaustedException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }
    }
}
