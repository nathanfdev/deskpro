<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\DownloadComment;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\DownloadComment as DownloadCommentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class DownloadCommentHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param DownloadComment $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new DownloadCommentModel($entity);
    }

    /**
     * @return string|string[]
     */
    public static function getClassNames()
    {
        return DownloadComment::class;
    }
}
