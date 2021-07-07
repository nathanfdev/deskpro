<?php

namespace Application\DeskPRO\EntityRepository;

/**
 * Class CommunityForumToStatus
 */
class CommunityForumToStatus extends AbstractEntityRepository
{
    /**
     * @param array $statuses
     * @param null $forum
     *
     * @return array
     */
    public function getForumStatus($statuses = [], $forum = null)
    {
        $forumStatuses = [];

        if (empty($statuses) || !$forum) {
            return $forumStatuses;
        }

        foreach ($statuses as $status) {
            $foundStatus = $this->findOneBy([
                'forum'  => $forum,
                'status' => $status,
            ]);

            if ($foundStatus) {
                $forumStatuses[] = $status;
            }
        }

        // if no specific statuses are set for this forum
        // then use all statuses as fallback

        return $forumStatuses ?: $statuses;
    }
}
