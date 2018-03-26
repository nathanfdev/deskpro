<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\Article;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Article as ArticleModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ArticleCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class ArticleHandler.
 */
class ArticleHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param Article $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        if ($context->getMappedClass(get_class($entity)) === ArticleCsv::class) {
            return new ArticleCsv($entity);
        }

        return new ArticleModel($entity);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Article::class;
    }
}
