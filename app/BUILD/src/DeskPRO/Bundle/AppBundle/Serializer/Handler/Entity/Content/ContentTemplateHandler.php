<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate as ContentTemplateEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentTemplate as ContentTemplateModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class ContentTemplateHandler.
 */
class ContentTemplateHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param ContentTemplateEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new ContentTemplateModel($entity);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ContentTemplateEntity::class;
    }
}
