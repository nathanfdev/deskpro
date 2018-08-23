<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceTarget;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAgentTarget.
 *
 * @ORM\EntityListeners({"DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceAgentTargetListener"})
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity
 */
class VoiceAgentTarget extends AbstractVoiceTarget
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
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
    public function getTargetDetails()
    {
        return [
            'id'   => $this->agent->getId(),
            'type' => 'agent',
            'name' => $this->agent->getName(),
        ];
    }
}
