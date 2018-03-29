<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TwitterAccountFriend extends AbstractEntityRepository
{
    /**
     * @param int $accountId
     * @param int $userId
     *
     * @return null|\Application\DeskPRO\Entity\TwitterAccountFriend
     */
    public function findOneByAccountIdAndUserId($accountId, $userId)
    {
        return $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:TwitterAccountFriend f
            WHERE f.account = :account AND f.user = :user
        ')->setParameters([
            'account' => $accountId,
            'user'    => $userId,
        ])->getOneOrNullResult();
    }

    public function getByAccountAndUsers($account_id, array $user_ids)
    {
        if (!$user_ids) {
            return [];
        }

        $output  = [];
        $results = $this->getEntityManager()->createQuery('
            SELECT f
            FROM   DeskPRO:TwitterAccountFriend f
            WHERE  f.account = :account AND f.user IN (:user)
        ')->setParameters([
            'account' => $account_id,
            'user'    => $user_ids,
        ])->execute();
        foreach ($results as $result) {
            $output[$result->user->getId()] = $result;
        }

        return $output;
    }
}
