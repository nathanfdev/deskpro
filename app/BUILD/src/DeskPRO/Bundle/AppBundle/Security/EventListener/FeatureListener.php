<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
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
     * Constructor.
     *
     * @param Features $features
     * @param Reader   $annotationReader
     */
    public function __construct(Features $features, Reader $annotationReader)
    {
        $this->features         = $features;
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

        return $this->features->hasFeature($feature);
    }
}
