<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataCache;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use DpTest\ApiTestCase;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\CheckMetadataTrait;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\ActionPermissionsClass;

class MetadataFactoryTest extends ApiTestCase
{
    use CheckMetadataTrait;

    /** @var MetadataFactory */
    protected $factory;

    public function setUp()
    {
        parent::setUp();
        $kernel        = $this->getApiKernel(true);
        $cache         = new MetadataCache($kernel->getContainer()->getParameter('kernel.cache_dir'), 'metadata_cache');
        $driver        = new ActionPermissionsDriver(new AnnotationReader());
        $this->factory = new MetadataFactory($driver, $cache);
    }

    public function testAnnotations()
    {
        $class_metadata = $this->factory->getMetadataForClass(ActionPermissionsClass::class);

        $this->checkMetadata($class_metadata);
    }

    public function testAnnotationsCacheRewrite()
    {
        $class_metadata = $this->factory->getMetadataForClass(ActionPermissionsClass::class, true);

        $this->checkMetadata($class_metadata);
    }
}
