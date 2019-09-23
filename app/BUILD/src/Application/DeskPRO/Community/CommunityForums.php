<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\CommunityForum;
use Doctrine\ORM\EntityManager;

class CommunityForums
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\CommunityForum[]
     */
    protected $communityForums;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads community forums data from the database.
     */
    private function preload()
    {
        if ($this->communityForums !== null) {
            return;
        }

        $this->communityForums = $this->em->getRepository('DeskPRO:CommunityForum')->getAll();
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->communityForums = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\CommunityForum
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:CommunityForum')->get($id);
    }

    /**
     * @param \Application\DeskPRO\Entity\CommunityForum|int $communityForum
     *
     * @return array
     */
    public function getNonAgentUserGroups($communityForum)
    {
        if (is_int($communityForum)) {
            $communityForum = $this->getById($communityForum);
        }

        return $this->em->getRepository('DeskPRO:CommunityForum')->getUserGroups($communityForum->getId(), false);
    }

    /**
     * @param \Application\DeskPRO\Entity\CommunityForum|int $communityForum
     *
     * @return array
     */
    public function getAgentUserGroups($communityForum)
    {
        if (is_int($communityForum)) {
            $communityForum = $this->getById($communityForum);
        }

        return $this->em->getRepository('DeskPRO:CommunityForum')->getUserGroups($communityForum->getId(), true);
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityForum[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->communityForums;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->communityForums);
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityForum
     */
    public function createNew()
    {
        return CommunityForum::createCommunityForum();
    }

    /**
     * @param array $newOrders
     */
    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $communityForums = $this->em->getRepository('DeskPRO:CommunityForum')->getByIds($newOrders);

        foreach ($newOrders as $id) {
            if (!isset($communityForums[$id])) {
                continue;
            }

            $communityForum                = $communityForums[$id];
            $communityForum->display_order = $x;

            $this->em->persist($communityForum);

            $x += 10;
        }

        $this->em->flush();
    }
}
