<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
