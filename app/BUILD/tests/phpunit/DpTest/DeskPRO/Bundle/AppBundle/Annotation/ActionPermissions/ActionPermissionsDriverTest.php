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
