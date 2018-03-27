<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class PersonTwitterUser extends AbstractEntityRepository
{
    public function getVerifiedPersonForTwitterUser($twitter_user_id)
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT t, p
            FROM DeskPRO:PersonTwitterUser t
            INNER JOIN t.person p
            WHERE t.twitter_user_id = ?0
                AND t.is_verified = true
        ')->setParameters([$twitter_user_id])->getOneOrNullResult();

        return $result ? $result->person : null;
    }
}
