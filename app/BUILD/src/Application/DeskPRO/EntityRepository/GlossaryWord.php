<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class GlossaryWord extends AbstractEntityRepository
{
    /**
     * Get a list of all words.
     *
     * @param int $brandId
     *
     * @return
     */
    public function getWords($brandId = 0)
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb->select(['id', 'word'])
            ->from('glossary_words')
            ->orderBy('word');

        if ($brandId) {
            $qb->andWhere($qb->expr()->eq('brand_id', $brandId));
        }

        $words = $this->getEntityManager()->getConnection()->fetchAllKeyValue($qb->getSQL());

        return $words;
    }

    /**
     * Get a list of all words containing the string.
     *
     * @param     $string
     * @param int $brandId
     *
     * @return
     */
    public function getWordsContaining($string, $brandId = 0)
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb->select(['id', 'word'])
            ->from('glossary_words')
            ->andWhere($qb->expr()->like('work', "%$string%"))
            ->orderBy('word');

        if ($brandId) {
            $qb->andWhere($qb->expr()->eq('brand_id', $brandId));
        }

        $words = $this->getEntityManager()->getConnection()->fetchAllKeyValue($qb->getSQL());

        return $words;
    }
}
