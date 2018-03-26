<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\AgentTeam as AgentTeamEntity;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentTeam.
 */
class AgentTeam
{
    /**
     * The unique team ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Team name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Team`s avatar.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Content\Avatar")
     *
     * @var Avatar
     */
    protected $avatar;

    /**
     * Agents belong to department.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]
     */
    protected $agents;

    /**
     * AgentTeam constructor.
     *
     * @param AgentTeamEntity $agent_team
     */
    public function __construct(AgentTeamEntity $agent_team)
    {
        $this->id     = $agent_team->getId();
        $this->name   = $agent_team->getName();
        $this->agents = $agent_team->getPersonList();
    }

    /**
     * @param Avatar $avatar
     *
     * @return $this
     */
    public function setAvatar(Avatar $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }
}
