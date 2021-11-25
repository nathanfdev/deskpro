<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\PortalBundle\Annotation\VerifyCsrf;
use DeskPRO\Bundle\PortalBundle\Form\Form\Extension\CsrfDoubleSubmitExtension;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class VerifyCsrfListener.
 */
class VerifyCsrfListener implements EventSubscriberInterface
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
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', -1],
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

        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();
        $className = ClassUtils::getClass($controller[0]);
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $annotations = $this->container->get('annotation_reader')->getMethodAnnotations($method);

        /** @var VerifyCsrf $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation instanceof VerifyCsrf
                && $request->request->get($annotation->name) !== $request->cookies->get(CsrfDoubleSubmitExtension::COOKIE_NAME)
            ) {
                throw new BadRequestHttpException();
            }
        }
    }
}
