<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ArticleComment;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\ArticleComment as ArticleCommentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class ArticleCommentHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param ArticleComment $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new ArticleCommentModel($entity);
    }

    /**
     * @return string|string[]
     */
    public static function getClassNames()
    {
        return ArticleComment::class;
    }
}
