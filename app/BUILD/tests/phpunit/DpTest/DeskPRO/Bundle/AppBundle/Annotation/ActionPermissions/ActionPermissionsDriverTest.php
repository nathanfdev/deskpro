<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\CheckMetadataTrait;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\AbstractActionPermissionsClass;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\ActionPermissionsClass;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\InterfaceActionPermissions;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\TraitActionPermissions;
use DpTest\DeskProTestCase;

class ActionPermissionsDriverTest extends DeskProTestCase
{
    use CheckMetadataTrait;

    /** @var ActionPermissionsDriver */
    protected $driver;

    public function setUp()
    {
        $this->driver = new ActionPermissionsDriver(new AnnotationReader());
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
        $this->checkMetadata($class_metadata);
    }
}
