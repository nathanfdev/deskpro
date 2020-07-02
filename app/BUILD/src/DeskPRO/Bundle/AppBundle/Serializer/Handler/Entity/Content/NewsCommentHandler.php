<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\NewsComment;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\NewsComment as NewsCommentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class NewsCommentHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param NewsComment $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new NewsCommentModel($entity);
    }

    /**
     * @return string|string[]
     */
    public static function getClassNames()
    {
        return NewsComment::class;
    }
}
