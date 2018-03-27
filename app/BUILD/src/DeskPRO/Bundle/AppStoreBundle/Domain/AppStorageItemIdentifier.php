<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class AppStorageItemIdentifier
{
    /** @var string */
    private $instanceId;

    /** @var string */
    private $name;

    /** @var AppStorage\EntityId */
    private $entityId;

    /**
     * @param string $instanceId
     * @param string $name
     * @param AppStorage\EntityId $entityId
     */
    public function __construct($instanceId, $name, AppStorage\EntityId $entityId)
    {
        $this->instanceId = $instanceId;
        $this->name = $name;
        $this->entityId = AppStorage\EntityId::convertToString($entityId);
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
