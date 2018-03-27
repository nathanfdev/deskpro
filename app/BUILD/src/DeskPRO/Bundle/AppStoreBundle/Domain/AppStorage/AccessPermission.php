<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

class AccessPermission
{
    /** @var string */
    private $accessLevel;

    /** @var string */
    private $permission;

    /**
     * @param string $accessLevel
     * @param string $permission
     */
    public function __construct($accessLevel, $permission)
    {
        if (empty($accessLevel) || empty($permission)) {
            throw new \DomainException('both access level and permission must be specified');
        }

        $this->accessLevel = $accessLevel;
        $this->permission  = $permission;
    }

    /**
     * @param $level
     *
     * @return bool
     */
    public function hasAccessLevel($level)
    {
        return $level === $this->getAccessLevel();
    }

    /**
     * @return string
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }
}
