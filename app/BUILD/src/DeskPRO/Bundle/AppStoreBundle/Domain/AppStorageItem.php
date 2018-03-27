<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class AppStorageItem
{
    private $identifier;

    private $securityDescriptor;

    private $value;

    /**
     * @param AppStorageItemIdentifier $identifier
     * @param AppStorage\SecurityDescriptor $securityDescriptor
     * @param string $value
     */
    public function __construct($identifier, $securityDescriptor, $value)
    {
        $this->identifier = $identifier;
        $this->securityDescriptor = $securityDescriptor;
        $this->value = $value;
    }

    /**
     * @return AppStorageItemIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $service
     * @param string $accessLevel
     * @return bool
     */
    public function confirmAccessLevelForService($service, $accessLevel)
    {
        $accessLevels = [Constants::ACCESS_LEVEL_READ, Constants::ACCESS_LEVEL_WRITE];
        return in_array($accessLevel, $accessLevels) && $this->securityDescriptor->allowsServiceAccess($service);
    }

    /**
     * @param string $personId
     * @param $accessLevel
     * @return bool
     */
    public function confirmAccessLevelForPerson($personId, $accessLevel)
    {
        if ($accessLevel === Constants::ACCESS_LEVEL_READ) {
            return $this->securityDescriptor->hasOwnership($personId)
                || $this->securityDescriptor->hasReadAccess(Constants::PERMISSION_EVERYONE);
        }

        if ($accessLevel === Constants::ACCESS_LEVEL_WRITE) {
            return $this->securityDescriptor->hasOwnership($personId)
                || $this->securityDescriptor->hasWriteAccess(Constants::PERMISSION_EVERYONE);
        }

        return false;
    }

    /**
     * @param AppStorage\SecurityDescriptor $newDescriptor
     * @return AppStorageItem
     */
    public function changeSecurityDescriptor( AppStorage\SecurityDescriptor $newDescriptor)
    {
        return new AppStorageItem($this->identifier, $newDescriptor, $this->value);
    }

    /**
     * @return AppStorage\SecurityDescriptor
     */
    public function getSecurityDescriptor()
    {
        return $this->securityDescriptor;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->identifier->getName();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
