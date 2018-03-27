<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Blob as BlobEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Blob as BlobModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class BlobHandler.
 */
class BlobHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return BlobEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param BlobEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new BlobModel($entity);
    }
}
