<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

class EntityId
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @param string $idString
     * @return EntityId|null
     */
    public static function parse($idString)
    {
        $separator = ':';
        $pieces = explode($separator, trim($idString));

        if (count($pieces) != 2) {
            return null;
        }

        return new EntityId($pieces[0], $pieces[1]);
    }

    /**
     * @param EntityId $entityId
     * @return string
     */
    public static function convertToString(EntityId $entityId)
    {
        $separator = ':';
        $pieces = [
            $entityId->getEntityType(),
            $entityId->getEntityId(),
        ];
        return  implode($separator, $pieces);
    }

    /**
     * @param string $entityType
     * @param string $entityId
     */
    public function __construct($entityType, $entityId)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

}
