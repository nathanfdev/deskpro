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
     * @param \Application\DeskPRO\Entity\Article $article
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function clearHistoryByEntity($article)
    {
        return $this->_em->getConnection()->executeUpdate('
            DELETE
            FROM articles_slug_history
            WHERE article_id = ?
        ', [$article->getId()]);
    }
}
