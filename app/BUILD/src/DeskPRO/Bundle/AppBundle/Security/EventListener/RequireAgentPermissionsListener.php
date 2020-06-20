<?php

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AgentPermissionListener.
 */
class RequireAgentPermissionsListener implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * Constructor.
     *
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 1024],
        ];
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @throws \ReflectionException
     *
     * @internal
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $className  = ClassUtils::getClass($controller[0]);

        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $classAnnotation  = $this->annotationReader->getClassAnnotation($object, RequireAgentPermissions::class);
        $methodAnnotation = $this->annotationReader->getMethodAnnotation($method, RequireAgentPermissions::class);

        if ($classAnnotation || $methodAnnotation) {
            $event->getRequest()->attributes->set('require_agent_permissions', true);

            if ($classAnnotation) {
                $event->getRequest()->attributes->set(
                    'admin_excluded_from_agent_permissions',
                    $classAnnotation->excludeAdmin
                );
            }

            if ($methodAnnotation) {
                $event->getRequest()->attributes->set(
                    'admin_excluded_from_agent_permissions',
                    $methodAnnotation->excludeAdmin
                );
            }
        }
    }
}
