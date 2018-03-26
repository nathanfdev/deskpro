<?php

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Features\FeaturesCollection;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use DpSys\Features;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class FeatureListener.
 */
class FeatureListener implements EventSubscriberInterface
{
    /**
     * @var Features
     */
    private $features;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var FeaturesCollection
     */
    private $featuresCollection;

    /**
     * Constructor.
     *
     * @param Features           $features
     * @param FeaturesCollection $featuresCollection
     * @param Reader             $annotationReader
     */
    public function __construct(Features $features, FeaturesCollection $featuresCollection, Reader $annotationReader)
    {
        $this->features           = $features;
        $this->featuresCollection = $featuresCollection;
        $this->annotationReader   = $annotationReader;
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
     * @internal
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $className  = ClassUtils::getClass($controller[0]);

        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $classAnnotation  = $this->annotationReader->getClassAnnotation($object, Feature::class);
        $methodAnnotation = $this->annotationReader->getMethodAnnotation($method, Feature::class);

        /** @var Feature $annotation */
        $annotation = $methodAnnotation ?: $classAnnotation;
        if ($annotation && !$this->hasAccess($annotation)) {
            throw new AccessDeniedHttpException('You are not allowed to access this feature.', null, 403);
        }
    }

    /**
     * @param Feature $annotation
     *
     * @return bool
     */
    private function hasAccess(Feature $annotation)
    {
        $feature = $annotation->getName();

        return $this->featuresCollection->hasFeature($feature)
            ? $this->features->hasBeta($feature)
            : $this->features->hasFeature($feature);
    }
}
