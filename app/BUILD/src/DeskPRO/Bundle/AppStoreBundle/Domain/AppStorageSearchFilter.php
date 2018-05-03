<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage\AccessPermission;

class AppStorageSearchFilter
{
    /** @var string */
    private $appId;

    /** @var AppStorage\EntityId */
    private $entityId;

    /** @var string[]|array */
    private $name;

    /** @var AccessPermission */
    private $accessPermission;

    /**
     * @param AppStorageItemIdentifier $id
     * @return AppStorageSearchFilter
     */
    public static function fromIdentifier(AppStorageItemIdentifier $id)
    {
        return new AppStorageSearchFilter(
            $id->getInstanceId(),
            $id->getEntityId(),
            $id->getName()
        );
    }

    /**
     * @param string $appId
     * @param string $entityId
     * @param string $name
     */
    public function __construct($appId, $entityId, $name = null)
    {
        $this->appId = $appId;
        $this->entityId = $entityId;
        $this->name = [(string) $name];
    }

    public function setAccessPermission(AccessPermission $accessPermission)
    {
        $this->accessPermission = $accessPermission;
    }

    /**
     * @return AccessPermission
     */
    public function getAccessPermision()
    {
        return $this->accessPermission;
    }

    /**
     * @return string
     */
    public function getApplicationInstanceId()
    {
        return (string) $this->appId;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return (string) $this->entityId;
    }

    /**
     * @return bool
     */
    public function hasName() {
        return !empty($this->name);
    }

    /**
     * @param string[]|array $nameList
     * @return AppStorageSearchFilter
     */
    public function setName($nameList)
    {
        $this->name = $nameList;
        return $this;
    }

    /**
     * @return string[]|array
     */
    public function getName()
    {
        return $this->name;
    }
}
