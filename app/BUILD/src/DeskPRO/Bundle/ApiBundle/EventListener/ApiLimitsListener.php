<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Controller\ExceptionController;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\AppBundle\Annotation\Limits\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Limits\LimitsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
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
     * {@inheritdoc}
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
        if (!$event->isMasterRequest()) {
            return;
        }

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

        $credentials = $token->getCredentials();
        if (!$credentials) {
            return;
        }

        $apiKey = $this->container->get('doctrine.orm.default_entity_manager')->getRepository(ApiKey::class)->findByKeyString($credentials);
        if (!$apiKey) {
            return;
        }

        /** @var LimitsService $limits */
        $limits = $this->container->get('api_limits.limits_service');
        $limits->checkLimits($apiKey);
        $limits->reduceLimits($apiKey);
    }
}
