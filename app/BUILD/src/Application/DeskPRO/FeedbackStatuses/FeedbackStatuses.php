<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackStatuses;

use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Doctrine\ORM\EntityManager;

class FeedbackStatuses
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\CommunityTopicStatusCategory[]
     */
    protected $active_statuses;

    /**
     * @var \Application\DeskPRO\Entity\CommunityTopicStatusCategory[]
     */
    protected $closed_statuses;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads feedback statuses data from the database.
     */
    private function preload()
    {
        if ($this->active_statuses !== null && $this->closed_statuses !== null) {
            return;
        }

        $this->active_statuses = $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->getActiveCategories();
        $this->closed_statuses = $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->getClosedCategories();
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->active_statuses = null;
        $this->closed_statuses = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\CommunityTopicStatusCategory
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->get($id);
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityTopicStatusCategory[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->active_statuses + $this->closed_statuses;
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityTopicStatusCategory[]
     */
    public function getActiveStatuses()
    {
        $this->preload();

        return $this->active_statuses;
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityTopicStatusCategory[]
     */
    public function getClosedStatuses()
    {
        $this->preload();

        return $this->closed_statuses;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->active_statuses) + count($this->closed_statuses);
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityTopicStatusCategory
     */
    public function createNew()
    {
        return CommunityTopicStatusCategory::createCommunityTopicStatusCategory();
    }

    /**
     * @param array $newOrders
     */
    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $feedback_statuses = $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->getByIds($newOrders);

        foreach ($newOrders as $id) {
            if (!isset($feedback_statuses[$id])) {
                continue;
            }

            $feedback_status                = $feedback_statuses[$id];
            $feedback_status->display_order = $x;

            $this->em->persist($feedback_status);

            $x += 10;
        }

        $this->em->flush();
    }
}
