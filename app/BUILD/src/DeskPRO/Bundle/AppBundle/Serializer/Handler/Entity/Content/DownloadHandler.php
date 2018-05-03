<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\Download;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Download as DownloadModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class DownloadHandler.
 */
class DownloadHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param Download $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        if ($context->getMappedClass(get_class($entity)) === ContentCsv::class) {
            return new ContentCsv($entity);
        }

        return new DownloadModel($entity);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Download::class;
    }
}
