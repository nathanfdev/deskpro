<?php

namespace DeskPRO\Bundle\AppBundle\Agent;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use Doctrine\ORM\EntityManager;

/**
 * Class AgentData.
 */
class AgentData
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $cachedNames;

    /**
     * @var array
     */
    private $cachedSelectbox;

    /**
     * Constructor.
     *
     * @param AvatarResolver $avatarResolver
     * @param EntityManager  $em
     */
    public function __construct(AvatarResolver $avatarResolver, EntityManager $em)
    {
        $this->avatarResolver = $avatarResolver;
        $this->em             = $em;
    }

    /**
     * @return array
     */
    public function getAgentNames()
    {
        if (!$this->cachedNames) {
            /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
            $personRepo        = $this->em->getRepository(Person::class);
            $this->cachedNames = $personRepo->getAgentNames();
        }

        return $this->cachedNames;
    }

    /**
     * @return array
     */
    public function getAgentAvatarList()
    {
        if (!$this->cachedSelectbox) {
            $names   = $this->getAgentNames();
            $avatars = $this->avatarResolver->getAvatars(array_keys($names), '{{size}}');

            foreach ($names as $agentId => $agentName) {
                $this->cachedSelectbox[$agentId] = new AgentAvatarModel($agentId, $agentName, $avatars[$agentId]);
            }
        }

        return $this->cachedSelectbox;
    }
}
