<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use DeskPRO\Bundle\ApiBundle\Controller\ExceptionController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use Doctrine\Common\Util\ClassUtils;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener as BaseControllerListener;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class ControllerListener.
 */
class ControllerListener extends BaseControllerListener
{
    /**
     * {@inheritdoc}
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils')
            ? ClassUtils::getClass($controller[0])
            : get_class(
                $controller[0]
            );
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $classAnnotations    = $this->reader->getClassAnnotations($object);
        $classConfigurations = !$controller[0] instanceof ExceptionController && $event->isMasterRequest()
            ? $this->getClassConfigurations($classAnnotations)
            : $this->getConfigurations($classAnnotations);
        $methodConfigurations = $this->getConfigurations($this->reader->getMethodAnnotations($method));

        $configurations = [];
        foreach (array_merge(array_keys($classConfigurations), array_keys($methodConfigurations)) as $key) {
            if (!array_key_exists($key, $classConfigurations)) {
                $configurations[$key] = $methodConfigurations[$key];
            } elseif (!array_key_exists($key, $methodConfigurations)) {
                $configurations[$key] = $classConfigurations[$key];
            } else {
                if (is_array($classConfigurations[$key])) {
                    if (!is_array($methodConfigurations[$key])) {
                        throw new \UnexpectedValueException('Configurations should both be an array or both not be an array');
                    }
                    $configurations[$key] = array_merge($classConfigurations[$key], $methodConfigurations[$key]);
                } else {
                    // method configuration overrides class configuration
                    $configurations[$key] = $methodConfigurations[$key];
                }
            }
        }

        $request = $event->getRequest();
        foreach ($configurations as $key => $attributes) {
            $request->attributes->set($key, $attributes);
        }
    }

    /**
     * @param array $annotations
     *
     * @return array
     */
    protected function getClassConfigurations(array $annotations)
    {
        if (!$this->alreadyHasAnnotation($annotations)) {
            $annotation    = new ApiUserContext(['value' => ApiUserContext::CONTEXT_AGENT]);
            $annotations[] = $annotation;
        }

        return parent::getConfigurations($annotations);
    }

    /**
     * @param $annotations
     *
     * @return bool
     */
    protected function alreadyHasAnnotation($annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ApiUserContext) {
                return true;
            }
        }

        return false;
    }
}
