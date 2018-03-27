<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ContentAbstract;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

class ArticlesLinkGenerator extends AbstractContentLinkGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof Article && $context === ObjectRouter::CONTEXT_PORTAL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBrand(ContentAbstract $object)
    {
        /* @var Article $object */

        /** @var ArticleCategory $category */
        $category = current($object->getCategories()->toArray());

        return $category ? $category->getBrand() : null;
    }
}
