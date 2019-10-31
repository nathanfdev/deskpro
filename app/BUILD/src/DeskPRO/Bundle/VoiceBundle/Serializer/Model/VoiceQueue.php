<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue as VoiceQueueEntity;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoiceQueue.
 */
class VoiceQueue
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var Department
     */
    private $department;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    private $brand;

    /**
     * @JMS\Expose()
     * @JMS\Type("collection<DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent>")
     *
     * @var VoiceQueueAgent[]
     */
    private $agents;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $routingModel;

    /**
     * @JMS\Expose()
     *
     * @var AbstractVoiceAsset
     */
    private $greetAsset;

    /**
     * @JMS\Expose()
     *
     * @var AbstractVoiceAsset
     */
    private $loopAsset;

    /**
     * @JMS\Expose()
     *
     * @var AbstractVoiceAsset
     */
    private $voicemailAsset;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var Department
     */
    private $voicemailDepartment;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $voicemailAgent;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var AgentTeam
     */
    private $voicemailAgentTeam;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $answerTimeout;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $voicemailTimeout;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $maxQueueSize;

    /**
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $recordingEnabled = true;

    /**
     * Constructor.
     *
     * @param VoiceQueueEntity $entity
     */
    public function __construct(VoiceQueueEntity $entity)
    {
        $this->id                  = $entity->getId();
        $this->name                = $entity->getName();
        $this->department          = $entity->getDepartment();
        $this->brand               = $entity->getBrand();
        $this->routingModel        = $entity->getRoutingModel();
        $this->answerTimeout       = $entity->getAnswerTimeout();
        $this->greetAsset          = $entity->getGreetAsset();
        $this->loopAsset           = $entity->getLoopAsset();
        $this->voicemailAsset      = $entity->getVoicemailAsset();
        $this->voicemailDepartment = $entity->getVoicemailDepartment();
        $this->voicemailAgent      = $entity->getVoicemailAgent();
        $this->voicemailAgentTeam  = $entity->getVoicemailAgentTeam();
        $this->voicemailTimeout    = $entity->getVoicemailTimeout();
        $this->maxQueueSize        = $entity->getMaxQueueSize();
        $this->recordingEnabled    = $entity->isRecordingEnabled();
    }

    /**
     * @param $agents
     */
    public function setAgents($agents)
    {
        $this->agents = $agents;
    }
}
