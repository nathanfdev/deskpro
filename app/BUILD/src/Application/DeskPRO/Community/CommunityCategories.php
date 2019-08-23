<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use Doctrine\ORM\EntityManager;

class CommunityCategories
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var CustomDefCommunityTopic[]
     */
    protected $parentChannel;

    /**
     * @var CustomDefCommunityTopic[]
     */
    protected $communityCategories;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads data from the database.
     *
     * @param Brand $brand
     */
    private function preload()
    {
        if ($this->communityCategories !== null) {
            return;
        }

        $this->communityCategories = [];

        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            $this->communityCategories = array_merge(
                $this->communityCategories,
                $this->em->getRepository(CustomDefCommunityTopic::class)->getAllFlatData($this->getParentChannel($brand))
            );
        }
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->communityCategories = null;
    }

    /**
     * @param int $id
     *
     * @return CustomDefCommunityTopic
     */
    public function getById($id)
    {
        return $this->em->getRepository(CustomDefCommunityTopic::class)->get($id);
    }

    /**
     * @return CustomDefCommunityTopic[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->communityCategories;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->communityCategories);
    }

    /**
     * @return CustomDefCommunityTopic
     */
    public function createNew()
    {
        return CustomDefCommunityTopic::createCommunityTopicCustomChannel();
    }

    /**
     * Attempts to create initial (parent) category for all hierarchy of categories.
     *
     * @param Brand $brand
     */
    protected function createInitialCommunityCategoryIfNotDefined(Brand $brand)
    {
        $this->parentChannel[$brand->getId()] = $this->em->getRepository(CustomDefCommunityTopic::class)->getChannelField($brand);

        if (!$this->parentChannel[$brand->getId()]) {
            $this->parentChannel[$brand->getId()]                = new CustomDefCommunityTopic();
            $this->parentChannel[$brand->getId()]->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
            $this->parentChannel[$brand->getId()]->setBrand($brand);
            $this->parentChannel[$brand->getId()]->title       = 'Category';
            $this->parentChannel[$brand->getId()]->sys_name    = 'cat';
            $this->parentChannel[$brand->getId()]->description = 'Category';

            $this->em->persist($this->parentChannel[$brand->getId()]);
            $this->em->flush();
        }
    }

    /**
     * @param Brand $brand
     *
     * @return CustomDefCommunityTopic
     */
    public function getParentChannel(Brand $brand = null)
    {
        if (!$brand) {
            return;
        }
        if (!isset($this->parentChannel[$brand->getId()])) {
            $this->createInitialCommunityCategoryIfNotDefined($brand);
        }

        return $this->parentChannel[$brand->getId()];
    }

    /**
     * @param array $newOrders
     */
    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $communityCategories = $this->em->getRepository('DeskPRO:CustomDefCommunityTopic')->getByIds($newOrders);

        foreach ($newOrders as $id) {
            if (!isset($communityCategories[$id])) {
                continue;
            }

            $communityCategory                = $communityCategories[$id];
            $communityCategory->display_order = $x;

            $this->em->persist($communityCategory);

            $x += 10;
        }

        $this->em->flush();
    }
}
