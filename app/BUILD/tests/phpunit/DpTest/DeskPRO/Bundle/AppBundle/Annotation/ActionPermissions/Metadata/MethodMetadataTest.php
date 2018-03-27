<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
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

    public function testSettersGetters()
    {
        $this->metadata->setModes(['key', 'token']);
        $this->metadata->setTags(['tag1', 'tag2']);
        $this->assertEquals(['key', 'token'], $this->metadata->getModes());
        $this->assertEquals(['tag1', 'tag2'], $this->metadata->getTags());
    }

    public function testSerialization()
    {
        $this->metadata->setModes(['key', 'token']);
        $this->metadata->setTags(['tag1', 'tag2']);
        $str      = serialize($this->metadata);
        $metadata = unserialize($str);
        $this->assertEquals(['key', 'token'], $metadata->getModes());
        $this->assertEquals(['tag1', 'tag2'], $metadata->getTags());
    }
}
