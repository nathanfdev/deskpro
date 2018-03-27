<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\News as NewsModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class NewsHandler.
 */
class NewsHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param News $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        if ($context->getMappedClass(get_class($entity)) === ContentCsv::class) {
            return new ContentCsv($entity);
        }

        return new NewsModel($entity);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return News::class;
    }
}
