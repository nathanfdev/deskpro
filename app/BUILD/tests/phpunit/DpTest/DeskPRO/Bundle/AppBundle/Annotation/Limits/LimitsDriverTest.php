<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\Limits;

use DeskPRO\Bundle\AppBundle\Annotation\Limits\LimitsDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\AbstractActionPermissionsClass;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\ActionPermissionsClass;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\InterfaceActionPermissions;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\TraitActionPermissions;
use DpTest\DeskProTestCase;

class LimitsDriverTest extends DeskProTestCase
{
    /** @var LimitsDriver */
    protected $driver;

    public function setUp()
    {
        $this->driver = new LimitsDriver(new AnnotationReader());
    }

    /**
     * @expectedException \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     */
    public function testAbstractClassParsing()
    {
        $this->driver->loadMetadataForClass(new \ReflectionClass(AbstractActionPermissionsClass::class));
    }

    /**
     * @expectedException \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     */
    public function testInterfaceParsing()
    {
        $this->driver->loadMetadataForClass(new \ReflectionClass(InterfaceActionPermissions::class));
    }

    /**
     * @expectedException \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     */
    public function testTraitParsing()
    {
        $this->driver->loadMetadataForClass(new \ReflectionClass(TraitActionPermissions::class));
    }

    public function testAnnotations()
    {
        $class_metadata = $this->driver->loadMetadataForClass(new \ReflectionClass(ActionPermissionsClass::class));
        $this->assertTrue($class_metadata->methodMetadata['inheritAction']->isLimitsDisabled());
    }
}
