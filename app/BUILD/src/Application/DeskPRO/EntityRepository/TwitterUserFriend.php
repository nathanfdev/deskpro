<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TwitterUserFriend extends AbstractEntityRepository
{
    public function getByUserAndFriends($user_id, array $friend_ids)
    {
        if (!$friend_ids) {
            return [];
        }

        $output  = [];
        $results = $this->getEntityManager()->createQuery('
            SELECT f, u
            FROM   DeskPRO:TwitterUserFriend f
            INNER JOIN f.friend_user u
            WHERE  f.user = :user AND f.friend_user IN (:friend)
        ')->setParameters([
            'user'   => $user_id,
            'friend' => $friend_ids,
        ])->execute();
        foreach ($results as $result) {
            $output[$result->friend_user->getId()] = $result;
        }

        return $output;
    }

    public function getFriendsForUser($user, $page = 1, $per_page = 25)
    {
        return $this->getEntityManager()->createQuery('
            SELECT f, u
            FROM DeskPRO:TwitterUserFriend f
            INNER JOIN f.friend_user u
            WHERE f.user = ?0
            ORDER BY f.display_order DESC
        ')->setFirstResult((max(1, $page) - 1) * $per_page)->setMaxResults($per_page)->execute([$user]);
    }
}
