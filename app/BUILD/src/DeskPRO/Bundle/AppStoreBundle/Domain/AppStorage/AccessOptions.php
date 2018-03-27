<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

use DeskPRO\Bundle\AppStoreBundle\Domain\Constants;

class AccessOptions
{
    /** @var string */
    private $readPermission;

    /** @var string */
    private $writePermission;

    /** @var bool */
    private $isBackendOnly;

    /**
     * @param string $readPermission
     * @param string $writePermission
     * @param bool   $isBackendOnly
     */
    public function __construct($readPermission = 'OWNER', $writePermission = 'OWNER', $isBackendOnly = false)
    {
        $this->readPermission  = $readPermission;
        $this->writePermission = $writePermission;
        $this->isBackendOnly   = $isBackendOnly;
    }

    public function isWorldAccessible()
    {
        return $this->getWritePermission() === $this->getReadPermission()
            && $this->getReadPermission() === Constants::PERMISSION_EVERYONE
        ;
    }

    /**
     * @return bool
     */
    public function isBackendOnly()
    {
        return $this->isBackendOnly;
    }

    /**
     * @return string
     */
    public function getReadPermission()
    {
        return $this->readPermission;
    }

    /**
     * @return string
     */
    public function getWritePermission()
    {
        return $this->writePermission;
    }
}
