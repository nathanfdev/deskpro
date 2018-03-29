<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceQueue.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_queues", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="task_queue_sid", columns={"task_queue_sid"}),
 *   @ORM\UniqueConstraint(name="name", columns={"name"})
 * })
 *
 * @ORM\EntityListeners({"DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceQueueListener"})
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity("taskQueueSid")
 * @UniqueEntity("name")
 *
 * @AppAssert\Voice\VoiceQueueAgentPermissions()
 */
class VoiceQueue implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const ROUTING_MODEL_AUTOMATIC      = 'automatic';
    const ROUTING_MODEL_LEAST_UTILIZED = 'least_utilized';
    const ROUTING_MODEL_SIMULRING      = 'simulring';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAccount", inversedBy="queues")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceAccount>")
     *
     * @Assert\NotNull()
     *
     * @var VoiceAccount
     */
    private $account;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="task_queue_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $taskQueueSid;

    /**
     * @ORM\JoinColumn(name="department_id")
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Department")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @Assert\NotNull()
     * @AppAssert\LeafDepartment()
     *
     * @var Department
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent", mappedBy="queue", cascade={"persist", "remove"}, fetch="EXTRA_LAZY", orphanRemoval=true)
     *
     * @JMS\Expose()
     * @JMS\Type("collection<DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent>")
     *
     * @Assert\Valid()
     *
     * @var VoiceQueueAgent[]|ArrayCollection
     */
    private $agents;

    /**
     * @ORM\Column(name="routing_model", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $routingModel;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset", cascade={"persist", "remove"}, fetch="EAGER", orphanRemoval=true)
     *
     * @JMS\Expose()
     *
     * @Assert\Valid()
     *
     * @var AbstractVoiceAsset
     */
    private $greetAsset;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset", cascade={"persist", "remove"}, fetch="EAGER", orphanRemoval=true)
     *
     * @JMS\Expose()
     *
     * @Assert\Valid()
     *
     * @var AbstractVoiceAsset
     */
    private $loopAsset;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset", cascade={"persist", "remove"}, fetch="EAGER", orphanRemoval=true)
     *
     * @JMS\Expose()
     *
     * @Assert\Valid()
     *
     * @var AbstractVoiceAsset
     */
    private $voicemailAsset;

    /**
     * @ORM\JoinColumn(name="voicemail_department")
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Department")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @AppAssert\LeafDepartment()
     *
     * @var Department
     */
    private $voicemailDepartment;

    /**
     * @ORM\JoinColumn(name="voicemail_agent")
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @AppAssert\Person\PersonType(type="agent")
     *
     * @var Person
     */
    private $voicemailAgent;

    /**
     * @ORM\JoinColumn(name="voicemail_agent_team")
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var AgentTeam
     */
    private $voicemailAgentTeam;

    /**
     * @ORM\Column(name="voicemail_timeout", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual("10")
     *
     * @var int
     */
    private $voicemailTimeout = 30;

    /**
     * @ORM\Column(name="max_queue_size", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $maxQueueSize = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->agents = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoiceAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return $this
     */
    public function setAccount(VoiceAccount $account = null)
    {
        $this->setModelField('account', $account);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getTaskQueueSid()
    {
        return $this->taskQueueSid;
    }

    /**
     * @param string $taskQueueSid
     *
     * @return $this
     */
    public function setTaskQueueSid($taskQueueSid)
    {
        $this->setModelField('taskQueueSid', $taskQueueSid);

        return $this;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department = null)
    {
        $this->setModelField('department', $department);

        return $this;
    }

    /**
     * @return VoiceQueueAgent[]|ArrayCollection
     */
    public function getAgents()
    {
        return $this->agents;
    }

    /**
     * @param VoiceQueueAgent $voiceAgent
     *
     * @return $this
     */
    public function addAgent(VoiceQueueAgent $voiceAgent)
    {
        if ($voiceAgent->getAgent()) {
            $existVoiceAgent = $this->agents->filter(function (VoiceQueueAgent $existVoiceAgent) use ($voiceAgent) {
                return $existVoiceAgent->getAgent() === $voiceAgent->getAgent();
            })->first();

            if ($existVoiceAgent instanceof VoiceQueueAgent) {
                $existVoiceAgent->setIsEnabled($voiceAgent->isEnabled());
            } else {
                $this->agents->add($voiceAgent);
                $voiceAgent->setQueue($this);
                $voiceAgent->getAgent()->getVoiceQueues()->add($voiceAgent);
            }
        } else {
            // to support validation
            $this->agents->add($voiceAgent);
            $voiceAgent->setQueue($this);
        }

        return $this;
    }

    /**
     * @param VoiceQueueAgent $voiceAgent
     *
     * @return $this
     */
    public function removeAgent(VoiceQueueAgent $voiceAgent)
    {
        $existVoiceAgent = $this->agents->filter(function (VoiceQueueAgent $existVoiceAgent) use ($voiceAgent) {
            return $existVoiceAgent->getAgent() === $voiceAgent->getAgent();
        })->first();

        if ($existVoiceAgent instanceof VoiceQueueAgent) {
            $this->agents->removeElement($existVoiceAgent);
            $existVoiceAgent->getAgent()->getVoiceQueues()->removeElement($this);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRoutingModel()
    {
        return $this->routingModel;
    }

    /**
     * @param string $routingModel
     *
     * @return $this
     */
    public function setRoutingModel($routingModel)
    {
        $this->setModelField('routingModel', $routingModel);

        return $this;
    }

    /**
     * @return AbstractVoiceAsset
     */
    public function getGreetAsset()
    {
        return $this->greetAsset;
    }

    /**
     * @param AbstractVoiceAsset $greetAsset
     *
     * @return $this
     */
    public function setGreetAsset(AbstractVoiceAsset $greetAsset = null)
    {
        $this->setModelField('greetAsset', $greetAsset);

        return $this;
    }

    /**
     * @return AbstractVoiceAsset
     */
    public function getLoopAsset()
    {
        return $this->loopAsset;
    }

    /**
     * @param AbstractVoiceAsset $loopAsset
     *
     * @return $this
     */
    public function setLoopAsset(AbstractVoiceAsset $loopAsset = null)
    {
        $this->setModelField('loopAsset', $loopAsset);

        return $this;
    }

    /**
     * @return AbstractVoiceAsset
     */
    public function getVoicemailAsset()
    {
        return $this->voicemailAsset;
    }

    /**
     * @param AbstractVoiceAsset $voicemailAsset
     *
     * @return $this
     */
    public function setVoicemailAsset(AbstractVoiceAsset $voicemailAsset = null)
    {
        $this->setModelField('voicemailAsset', $voicemailAsset);

        return $this;
    }

    /**
     * @return Department
     */
    public function getVoicemailDepartment()
    {
        return $this->voicemailDepartment;
    }

    /**
     * @param Department $voicemailDepartment
     *
     * @return $this
     */
    public function setVoicemailDepartment(Department $voicemailDepartment = null)
    {
        $this->setModelField('voicemailDepartment', $voicemailDepartment);

        return $this;
    }

    /**
     * @return Person
     */
    public function getVoicemailAgent()
    {
        return $this->voicemailAgent;
    }

    /**
     * @param Person $voicemailAgent
     *
     * @return $this
     */
    public function setVoicemailAgent(Person $voicemailAgent = null)
    {
        $this->setModelField('voicemailAgent', $voicemailAgent);

        return $this;
    }

    /**
     * @return AgentTeam
     */
    public function getVoicemailAgentTeam()
    {
        return $this->voicemailAgentTeam;
    }

    /**
     * @param AgentTeam $voicemailAgentTeam
     *
     * @return $this
     */
    public function setVoicemailAgentTeam(AgentTeam $voicemailAgentTeam = null)
    {
        $this->setModelField('voicemailAgentTeam', $voicemailAgentTeam);

        return $this;
    }

    /**
     * @return int
     */
    public function getVoicemailTimeout()
    {
        return $this->voicemailTimeout;
    }

    /**
     * @param int $voicemailTimeout
     *
     * @return $this
     */
    public function setVoicemailTimeout($voicemailTimeout)
    {
        $this->setModelField('voicemailTimeout', $voicemailTimeout);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxQueueSize()
    {
        return $this->maxQueueSize;
    }

    /**
     * @param int $maxQueueSize
     *
     * @return $this
     */
    public function setMaxQueueSize($maxQueueSize)
    {
        $this->setModelField('maxQueueSize', $maxQueueSize);

        return $this;
    }
}
