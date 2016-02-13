<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Mock\ActionPermissionsClass;
use DpTest\DeskProTestCase;

class MethodMetadataTest extends DeskProTestCase
{
    /** @var  MethodMetadata */
    protected $metadata;

    public function setUp()
    {
        $reflection     = new \ReflectionClass(ActionPermissionsClass::class);
        $this->metadata = new MethodMetadata($reflection->getName(), 'overrideBoth');
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
