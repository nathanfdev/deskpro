<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TwitterAccountSearch extends AbstractEntityRepository
{
    public function getExistingSearch($term, \Application\DeskPRO\Entity\TwitterAccount $account)
    {
        return $this->getEntityManager()->createQuery('
            SELECT s
            FROM DeskPRO:TwitterAccountSearch s
            WHERE s.term = ?0 AND s.account = ?1
        ')->setParameters([$term, $account])->getOneOrNullResult();
    }

    public function getExistingSearchStatus(\Application\DeskPRO\Entity\TwitterAccountSearch $search, \Application\DeskPRO\Entity\TwitterAccountStatus $account_status)
    {
        return $this->getEntityManager()->createQuery('
            SELECT s
            FROM DeskPRO:TwitterAccountSearchStatus s
            WHERE s.search = ?0 AND s.account_status = ?1
        ')->setParameters([$search, $account_status])->getOneOrNullResult();
    }
}
