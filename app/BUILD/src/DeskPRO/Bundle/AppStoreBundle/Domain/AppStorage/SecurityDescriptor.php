<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class SecurityDescriptor
{
    /** @var string */
    private $ownerId;

    /** @var string */
    private $read;

    /** @var string */
    private $write;

    /** @var boolean */
    private $isBackendOnly;

    /**
     * @param string $owner
     * @param string $read
     * @param string $write
     * @param boolean $isBackendOnly
     */
    public function __construct($owner, $read, $write, $isBackendOnly)
    {
        $this->ownerId = $owner;
        $this->read = $read;
        $this->write = $write;
        $this->isBackendOnly = $isBackendOnly;
    }

    /**
     * @param string $personId
     * @return bool
     */
    public function hasOwnership($personId) {
        return $this->ownerId === $personId;
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasReadAccess($permission) {
        $response = $this->read === Domain\Constants::PERMISSION_EVERYONE
            || ($this->read === $permission && $this->read === Domain\Constants::PERMISSION_OWNER)
        ;
        return $response;
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasWriteAccess($permission) {
        $response = $this->write === Domain\Constants::PERMISSION_EVERYONE
            || ($this->write === $permission && $this->write === Domain\Constants::PERMISSION_OWNER)
        ;
        return $response;
    }

    public function allowsServiceAccess($service)
    {
        $backendServices = [Domain\Constants::ACCESS_SERVICE_PROXY];
        return !$this->isBackendOnly || in_array($service, $backendServices);
    }

    /**
     * @return bool
     */
    public function allowOnlyBackendServiceAccess() {
        return $this->isBackendOnly;
    }

    /**
     * @param AccessOptions $options
     * @return SecurityDescriptor
     */
    public function changeAccessLevels(AccessOptions $options)
    {
        $read = $options->getReadPermission();
        $write = $options->getWritePermission();
        $isBackendOnly = $options->isBackendOnly();

        return new SecurityDescriptor($this->ownerId, $read, $write, $isBackendOnly);
    }

    /**
     * @param string $read
     * @param string $write
     * @return SecurityDescriptor
     */
    public function changeReadWriteAccessLevel($read, $write)
    {
        return new SecurityDescriptor($this->ownerId, $read, $write, $this->isBackendOnly);
    }

    /**
     * @param string $level
     * @return SecurityDescriptor
     */
    public function changeReadAccessLevel($level)
    {
        return $this->changeReadWriteAccessLevel($level, $this->write);
    }

    /**
     * @param string $level
     * @return SecurityDescriptor
     */
    public function changeWriteAccessLevel($level)
    {
        return $this->changeReadWriteAccessLevel($level, $this->write);
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return AccessOptions
     */
    public function getAccessOptions() {
        return new AccessOptions($this->read, $this->write, $this->isBackendOnly);
    }
}
