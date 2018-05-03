<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\Limits;

use DeskPRO\Bundle\AppBundle\Annotation\Driver\AbstractDriver;
use DeskPRO\Bundle\AppBundle\Annotation\Limits\Annotation\ApiDisableLimits;
use DeskPRO\Bundle\AppBundle\Annotation\Limits\Metadata\MethodMetadata;
use Metadata\MergeableClassMetadata;

/**
 * Class LimitsDriver.
 */
class LimitsDriver extends AbstractDriver
{
    protected function loadInternal(\ReflectionClass $class, MergeableClassMetadata $classMetadata)
    {
        foreach ($this->getMethods($class) as $method) {
            /* @var \ReflectionMethod $method */
            $methodMetadata  = new MethodMetadata($class->getName(), $method->getName());
            $disabled_limits = $this->reader->getMethodAnnotation($method, ApiDisableLimits::class);
            if ($disabled_limits && $disabled_limits instanceof ApiDisableLimits) {
                $methodMetadata->disableLimits();
            }
            $classMetadata->addMethodMetadata($methodMetadata);
        }

        return $classMetadata;
    }
}
