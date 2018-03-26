<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DpTest\DeskProTestCase;

class AccessOptionsTest extends DeskProTestCase
{
    public function testDefaultAccessOptions()
    {
        $accessOptions = new Domain\AppStorage\AccessOptions();

        $this->assertEquals(
            Domain\Constants::PERMISSION_OWNER,
            $accessOptions->getReadPermission(),
            sprintf('default read access should be %s', Domain\Constants::PERMISSION_OWNER)
        );
        $this->assertEquals(
            Domain\Constants::PERMISSION_OWNER,
            $accessOptions->getWritePermission(),
            sprintf('default write access should be %s', Domain\Constants::PERMISSION_OWNER)
        );
        $this->assertFalse(
            $accessOptions->isBackendOnly(),
            sprintf('default system access should be backend only')
        );
    }
}
