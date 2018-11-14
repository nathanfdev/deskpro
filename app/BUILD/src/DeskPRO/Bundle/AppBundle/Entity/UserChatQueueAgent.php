<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserChatQueueAgent.
 *
 * @ORM\Entity()
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserChatQueueAgent extends AbstractUserChatQueueTarget
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\SerializedName("target")
     *
     * @Assert\NotNull()
     * @AppAssert\Person\PersonType(type="agent")
     *
     * @var Person
     */
    protected $agent;

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function setAgent(Person $agent = null)
    {
        $this->setModelField('agent', $agent);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'type' => AbstractUserChatQueueTarget::TYPE_AGENT,
            'id'   => $this->agent->getId(),
        ];
    }
}
