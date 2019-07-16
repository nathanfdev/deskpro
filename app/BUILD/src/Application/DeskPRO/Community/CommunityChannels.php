<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\CommunityChannel;
use Doctrine\ORM\EntityManager;

class CommunityChannels
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\CommunityChannel[]
     */
    protected $communityChannels;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads community channels data from the database.
     */
    private function preload()
    {
        if ($this->communityChannels !== null) {
            return;
        }

        $this->communityChannels = $this->em->getRepository('DeskPRO:CommunityChannel')->getAll();
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->communityChannels = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\CommunityChannel
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:CommunityChannel')->get($id);
    }

    /**
     * @param \Application\DeskPRO\Entity\CommunityChannel|int $communityChannel
     *
     * @return array
     */
    public function getNonAgentUserGroups($communityChannel)
    {
        if (is_int($communityChannel)) {
            $communityChannel = $this->getById($communityChannel);
        }

        return $this->em->getRepository('DeskPRO:CommunityChannel')->getUserGroups($communityChannel->getId(), false);
    }

    /**
     * @param \Application\DeskPRO\Entity\CommunityChannel|int $communityChannel
     *
     * @return array
     */
    public function getAgentUserGroups($communityChannel)
    {
        if (is_int($communityChannel)) {
            $communityChannel = $this->getById($communityChannel);
        }

        return $this->em->getRepository('DeskPRO:CommunityChannel')->getUserGroups($communityChannel->getId(), true);
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityChannel[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->communityChannels;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->communityChannels);
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityChannel
     */
    public function createNew()
    {
        return CommunityChannel::createCommunityChannel();
    }

    /**
     * @param array $newOrders
     */
    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $communityChannels = $this->em->getRepository('DeskPRO:CommunityChannel')->getByIds($newOrders);

        foreach ($newOrders as $id) {
            if (!isset($communityChannels[$id])) {
                continue;
            }

            $communityChannel                = $communityChannels[$id];
            $communityChannel->display_order = $x;

            $this->em->persist($communityChannel);

            $x += 10;
        }

        $this->em->flush();
    }
}
