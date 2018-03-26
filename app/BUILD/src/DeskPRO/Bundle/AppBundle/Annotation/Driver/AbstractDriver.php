<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\Driver;

use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use Doctrine\Common\Annotations\AnnotationReader;
use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
        $this->reader->addGlobalIgnoredName('SWG\Api');
        $this->reader->addGlobalIgnoredName('SWG\Operation');
        $this->reader->addGlobalIgnoredName('SWG\Parameters');
        $this->reader->addGlobalIgnoredName('SWG\Parameter');
        $this->reader->addGlobalIgnoredName('SWG\ResponseMessage');
    }

    /**
     * @param \ReflectionClass $class
     *
     * @throws AbstractClassException - it should be thrown only when using cli cache:warmup
     *
     * @return MergeableClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if ($class->isAbstract() || $class->isTrait() || $class->isInterface()) {
            throw new AbstractClassException(sprintf('Skip [ %s ] class. It\'s abstract, trait or interface', $class->getName()));
        }
        $classMetadata = new MergeableClassMetadata($class->getName());

        return $this->loadInternal($class, $classMetadata);
    }

    abstract protected function loadInternal(\ReflectionClass $class, MergeableClassMetadata $classMetadata);

    protected function getMethods(\ReflectionClass $class)
    {
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        return array_filter($methods, function ($method) {
            return strcasecmp(substr($method->getName(), -6 /* word 'action' length */), 'action') === 0;
        }
        );
    }
}
