<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use Doctrine\ORM\EntityManager;

class CustomCommunityChannels
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
    protected $communityCustomChannels;

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
        if ($this->communityCustomChannels !== null) {
            return;
        }

        $this->communityCustomChannels = [];

        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            $this->communityCustomChannels = array_merge(
                $this->communityCustomChannels,
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
        $this->communityCustomChannels = null;
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

        return $this->communityCustomChannels;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->communityCustomChannels);
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
    protected function createInitialCommunityCustomChannelIfNotDefined(Brand $brand)
    {
        $this->parentChannel[$brand->getId()] = $this->em->getRepository(CustomDefCommunityTopic::class)->getChannelField($brand);

        if (!$this->parentChannel[$brand->getId()]) {
            $this->parentChannel[$brand->getId()]                = new CustomDefCommunityTopic();
            $this->parentChannel[$brand->getId()]->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
            $this->parentChannel[$brand->getId()]->setBrand($brand);
            $this->parentChannel[$brand->getId()]->title       = 'Channel';
            $this->parentChannel[$brand->getId()]->sys_name    = 'chan';
            $this->parentChannel[$brand->getId()]->description = 'Channel';

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
            $this->createInitialCommunityCustomChannelIfNotDefined($brand);
        }

        return $this->parentChannel[$brand->getId()];
    }

    /**
     * @param array $newOrders
     */
    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $communityCustomChannels = $this->em->getRepository('DeskPRO:CustomDefCommunityTopic')->getByIds($newOrders);

        foreach ($newOrders as $id) {
            if (!isset($communityCustomChannels[$id])) {
                continue;
            }

            $communityCustomChannel                = $communityCustomChannels[$id];
            $communityCustomChannel->display_order = $x;

            $this->em->persist($communityCustomChannel);

            $x += 10;
        }

        $this->em->flush();
    }
}
