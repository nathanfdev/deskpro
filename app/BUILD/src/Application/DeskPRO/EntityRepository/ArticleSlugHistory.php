<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class ArticleSlugHistory extends AbstractEntityRepository
{
    /**
     * @param int $articleId
     */
    public function deleteByArticleId($articleId)
    {
        $this->_em->createQuery('
            DELETE 
            FROM DeskPRO:ArticleSlugHistory ash
            WHERE ash.article = ?0
        ')
            ->execute([$articleId]);
    }
}
