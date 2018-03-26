<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DpTest\DeskProTestCase;

class AppStorageItemTest extends DeskProTestCase
{
    public function testCheckAccessLevelForSystemReturnsTrue()
    {
        $value = null;
        $read  = Domain\Constants::PERMISSION_OWNER;
        $write = Domain\Constants::PERMISSION_OWNER;
        $owner = 1;

        $identifier = new Domain\AppStorageItemIdentifier('1', 'name', Domain\AppStorage\EntityId::parse('ticket:1'));
        $trials     = [
            [
                'isBackendOnly' => true,

                'level'   => Domain\Constants::ACCESS_LEVEL_READ,
                'service' => Domain\Constants::ACCESS_SERVICE_PROXY,
            ],
            [
                'isBackendOnly' => true,

                'level'   => Domain\Constants::ACCESS_LEVEL_WRITE,
                'service' => Domain\Constants::ACCESS_SERVICE_PROXY,
            ],
            [
                'isBackendOnly' => false,

                'level'   => Domain\Constants::ACCESS_LEVEL_WRITE,
                'service' => Domain\Constants::ACCESS_SERVICE_API,
            ],
            [
                'isBackendOnly' => false,

                'level'   => Domain\Constants::ACCESS_LEVEL_READ,
                'service' => Domain\Constants::ACCESS_SERVICE_API,
            ],
            [
                'isBackendOnly' => false,

                'level'   => Domain\Constants::ACCESS_LEVEL_WRITE,
                'service' => Domain\Constants::ACCESS_SERVICE_PROXY,
            ],
            [
                'isBackendOnly' => false,

                'level'   => Domain\Constants::ACCESS_LEVEL_READ,
                'service' => Domain\Constants::ACCESS_SERVICE_PROXY,
            ],
        ];

        $isBackendOnly = null;
        $level         = null;
        $service       = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $securityDescriptor = new Domain\AppStorage\SecurityDescriptor($owner, $read, $write, $isBackendOnly);
            $state              = new Domain\AppStorageItem($identifier, $securityDescriptor, $value);

            $actualResult = $state->confirmAccessLevelForService($service, $level);
            $this->assertTrue($actualResult, sprintf('service %s should have %s access', $service, $level));
        }
    }

    public function testCheckAccessLevelForSystemReturnsFalse()
    {
        $value = null;
        $read  = Domain\Constants::PERMISSION_OWNER;
        $write = Domain\Constants::PERMISSION_OWNER;
        $owner = 1;

        $identifier = new Domain\AppStorageItemIdentifier('1', 'name', Domain\AppStorage\EntityId::parse('ticket:1'));
        $trials     = [
            [ // random string instead of proper access level
                'isBackendOnly' => true,

                'level'   => strrev(Domain\Constants::ACCESS_LEVEL_READ),
                'service' => Domain\Constants::ACCESS_SERVICE_PROXY,
            ],
            [ // random string instead of proper access level
                'isBackendOnly' => false,

                'level'   => strrev(Domain\Constants::ACCESS_LEVEL_READ),
                'service' => Domain\Constants::ACCESS_SERVICE_API,
            ],
            [ // non backend service trying to read
                'isBackendOnly' => true,

                'level'   => Domain\Constants::ACCESS_LEVEL_READ,
                'service' => Domain\Constants::ACCESS_SERVICE_API,
            ],
            [ // non backend service trying to write
                'isBackendOnly' => true,

                'level'   => Domain\Constants::ACCESS_LEVEL_WRITE,
                'service' => Domain\Constants::ACCESS_SERVICE_API,
            ],
        ];

        $isBackendOnly = null;
        $level         = null;
        $service       = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $securityDescriptor = new Domain\AppStorage\SecurityDescriptor($owner, $read, $write, $isBackendOnly);
            $state              = new Domain\AppStorageItem($identifier, $securityDescriptor, $value);

            $actualResult = $state->confirmAccessLevelForService($service, $level);
            $this->assertFalse($actualResult, sprintf('service %s should not have %s access', $service, $level));
        }
    }

    public function testCheckAccessLevelForPersonReturnsTrue()
    {
        $value         = null;
        $isBackendOnly = true;
        $identifier    = new Domain\AppStorageItemIdentifier('1', 'name', Domain\AppStorage\EntityId::parse('ticket:1'));
        $trials        = [
            [
                'read'  => Domain\Constants::PERMISSION_OWNER,
                'write' => Domain\Constants::PERMISSION_OWNER,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_READ,
                'personId' => 1,
            ],
            [
                'read'  => Domain\Constants::PERMISSION_EVERYONE,
                'write' => Domain\Constants::PERMISSION_OWNER,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_READ,
                'personId' => 2,
            ],
            [
                'read'  => Domain\Constants::PERMISSION_OWNER,
                'write' => Domain\Constants::PERMISSION_OWNER,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_WRITE,
                'personId' => 1,
            ],
            [
                'read'  => Domain\Constants::PERMISSION_OWNER,
                'write' => Domain\Constants::PERMISSION_EVERYONE,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_WRITE,
                'personId' => 2,
            ],
        ];

        $read     = null;
        $write    = null;
        $owner    = null;
        $level    = null;
        $personId = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $securityDescriptor = new Domain\AppStorage\SecurityDescriptor($owner, $read, $write, $isBackendOnly);
            $state              = new Domain\AppStorageItem($identifier, $securityDescriptor, $value);

            $actualResult = $state->confirmAccessLevelForPerson($personId, $level);
            $this->assertTrue($actualResult, sprintf('person %s should have access level %s', $personId, $level));
        }
    }

    public function testCheckAccessLevelForPersonReturnsFalse()
    {
        $value         = null;
        $isBackendOnly = true;
        $identifier    = new Domain\AppStorageItemIdentifier('1', 'name', Domain\AppStorage\EntityId::parse('ticket:1'));
        $trials        = [
            [
                'read'  => Domain\Constants::PERMISSION_OWNER,
                'write' => Domain\Constants::PERMISSION_OWNER,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_READ,
                'personId' => 2,
            ],
            [
                'read'  => Domain\Constants::PERMISSION_OWNER,
                'write' => Domain\Constants::PERMISSION_EVERYONE,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_READ,
                'personId' => 2,
            ],
            [
                'read'  => Domain\Constants::PERMISSION_EVERYONE,
                'write' => Domain\Constants::PERMISSION_OWNER,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_WRITE,
                'personId' => 2,
            ],
            [
                'read'  => Domain\Constants::PERMISSION_OWNER,
                'write' => Domain\Constants::PERMISSION_OWNER,
                'owner' => 1,

                'level'    => Domain\Constants::ACCESS_LEVEL_WRITE,
                'personId' => 2,
            ],
        ];

        $read     = null;
        $write    = null;
        $owner    = null;
        $level    = null;
        $personId = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $securityDescriptor = new Domain\AppStorage\SecurityDescriptor($owner, $read, $write, $isBackendOnly);
            $state              = new Domain\AppStorageItem($identifier, $securityDescriptor, $value);

            $actualResult = $state->confirmAccessLevelForPerson($personId, $level);
            $this->assertFalse(
                $actualResult,
                sprintf('permissions r=%s,w=%s,o=%s should deny %s access to person %s ', $read, $write, $owner, $level, $personId)
            );
        }
    }
}
