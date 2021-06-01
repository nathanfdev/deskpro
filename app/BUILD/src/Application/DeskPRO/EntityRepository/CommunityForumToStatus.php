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
        $forumStatus = [];

        if (empty($statuses) || !$forum) {
            return $forumStatus;
        }

        foreach ($statuses as $status) {
            $foundStatus = $this->findOneBy([
                'forum'  => $forum,
                'status' => $status->getId(),
            ]);

            if ($foundStatus) {
                $forumStatus[] = $status;
            }
        }

        return $forumStatus;
    }
}
