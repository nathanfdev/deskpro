<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserChatQueueAgentTeam.
 *
 * @ORM\Entity()
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserChatQueueAgentTeam extends AbstractUserChatQueueTarget
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     * @ORM\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     * @JMS\SerializedName("target")
     *
     * @Assert\NotNull()
     *
     * @var AgentTeam
     */
    protected $agentTeam;

    /**
     * @return AgentTeam
     */
    public function getAgentTeam()
    {
        return $this->agentTeam;
    }

    /**
     * @param AgentTeam $agentTeam
     *
     * @return $this
     */
    public function setAgentTeam(AgentTeam $agentTeam = null)
    {
        $this->setModelField('agentTeam', $agentTeam);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'type' => AbstractUserChatQueueTarget::TYPE_AGENT_TEAM,
            'id'   => $this->agentTeam->getId(),
        ];
    }
}
