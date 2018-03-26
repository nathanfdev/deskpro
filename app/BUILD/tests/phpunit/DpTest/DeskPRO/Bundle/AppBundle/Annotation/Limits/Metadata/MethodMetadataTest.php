<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\Limits\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\Limits\Metadata\MethodMetadata;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\ActionPermissionsClass;
use DpTest\DeskProTestCase;

class MethodMetadataTest extends DeskProTestCase
{
    /** @var MethodMetadata */
    protected $metadata;

    public function setUp()
    {
        $reflection     = new \ReflectionClass(ActionPermissionsClass::class);
        $this->metadata = new MethodMetadata($reflection->getName(), 'overrideBothAction');
    }

    public function testLimitsDisable()
    {
        $this->metadata->disableLimits();
        $this->assertTrue($this->metadata->isLimitsDisabled());
    }

    public function testSerialization()
    {
        $this->metadata->disableLimits();
        $str      = serialize($this->metadata);
        $metadata = unserialize($str);
        $this->assertTrue($metadata->isLimitsDisabled());
    }
}
