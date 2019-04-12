<?php

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\AgentPermission;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AgentPermissionListener implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

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

        $user = $controller[0]->getUser();

        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $classAnnotation  = $this->annotationReader->getClassAnnotation($object, AgentPermission::class);
        $methodAnnotation = $this->annotationReader->getMethodAnnotation($method, AgentPermission::class);

        /** @var AgentPermission $annotation */
        $annotation = $methodAnnotation ?: $classAnnotation;
        if ($annotation && !$this->hasAccess($annotation, $user)) {
            throw new AccessDeniedHttpException('You have not the permission to do this action.', null, 403);
        }
    }

    /**
     * @param AgentPermission $annotation
     * @param Person          $user
     *
     * @return bool
     */
    private function hasAccess(AgentPermission $annotation, Person $user)
    {
        $permission = $annotation->getName();

        return $user->hasPerm($permission);
    }
}
