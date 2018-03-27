<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TwitterUserFollower extends AbstractEntityRepository
{
    public function getByUserAndFollowers($user_id, array $follower_ids)
    {
        if (!$follower_ids) {
            return [];
        }

        $output  = [];
        $results = $this->getEntityManager()->createQuery('
            SELECT f, u
            FROM   DeskPRO:TwitterUserFollower f
            INNER JOIN f.follower_user u
            WHERE  f.user = :user AND f.follower_user IN (:follower)
        ')->setParameters([
            'user'     => $user_id,
            'follower' => $follower_ids,
        ])->execute();
        foreach ($results as $result) {
            $output[$result->follower_user->getId()] = $result;
        }

        return $output;
    }

    public function getFollowersForUser($user, $page = 1, $per_page = 25)
    {
        return $this->getEntityManager()->createQuery('
            SELECT f, u
            FROM DeskPRO:TwitterUserFollower f
            INNER JOIN f.follower_user u
            WHERE f.user = ?0
            ORDER BY f.display_order DESC
        ')->setFirstResult((max(1, $page) - 1) * $per_page)->setMaxResults($per_page)->execute([$user]);
    }
}
