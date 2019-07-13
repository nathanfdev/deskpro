<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\EntityRepository\CommunityTopicStatusCategory as CommunityTopicStatusCategoryRepository;
use Doctrine\ORM\EntityManager;

class CommunityStatuses
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CommunityTopicStatusCategory[]
     */
    protected $active_statuses;

    /**
     * @var CommunityTopicStatusCategory[]
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
        /** @var CommunityTopicStatusCategoryRepository $communityTopicStatusCategoryRepo */
        $communityTopicStatusCategoryRepo = $this->em->getRepository(CommunityTopicStatusCategory::class);
        $this->active_statuses            = $communityTopicStatusCategoryRepo->getActiveCategories();
        $this->closed_statuses            = $communityTopicStatusCategoryRepo->getClosedCategories();
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
     * @return CommunityTopicStatusCategory
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->get($id);
    }

    /**
     * @return CommunityTopicStatusCategory[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->active_statuses + $this->closed_statuses;
    }

    /**
     * @return CommunityTopicStatusCategory[]
     */
    public function getActiveStatuses()
    {
        $this->preload();

        return $this->active_statuses;
    }

    /**
     * @return CommunityTopicStatusCategory[]
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
     * @return CommunityTopicStatusCategory
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

        $communityStatuses = $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->getByIds($newOrders);

        foreach ($newOrders as $id) {
            if (!isset($communityStatuses[$id])) {
                continue;
            }

            $communityStatus                = $communityStatuses[$id];
            $communityStatus->display_order = $x;

            $this->em->persist($communityStatus);

            $x += 10;
        }

        $this->em->flush();
    }
}
