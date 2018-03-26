<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Article as ArticleEntity;

class ArticleCsv extends ContentCsv
{
    /**
     * @param ArticleEntity $entity
     *
     * @return string
     */
    protected function getCategory($entity)
    {
        $categoryNames = [];
        $categories    = $entity->getCategories();
        foreach ($categories as $category) {
            $categoryNames[] = $category->getTitle();
        }

        return implode(', ', $categoryNames);
    }
}
