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
