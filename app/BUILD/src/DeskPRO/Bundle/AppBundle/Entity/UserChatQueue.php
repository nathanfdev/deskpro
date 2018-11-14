<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Department;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserChatQueue.
 *
 * @ORM\Entity()
 * @ORM\Table(name="user_chat_queues")
 * @ORM\EntityListeners({"DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine\UserChatQueueListener"})
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserChatQueue implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const ROUTING_MODEL_ROUND_ROBIN    = 'round_robin';
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
     * @ORM\Column(name="answer_timeout", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var int
     */
    private $answerTimeout = 60;

    /**
     * @ORM\Column(name="is_all_agents", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isAllAgents;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget", mappedBy="queue", cascade={"persist", "remove"}, fetch="EXTRA_LAZY", orphanRemoval=true)
     *
     * @JMS\Expose()
     * @JMS\Type("collection<DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget>")
     *
     * @Assert\Valid()
     *
     * @var AbstractUserChatQueueTarget[]|ArrayCollection
     */
    private $targets;

    /**
     * @ORM\OneToMany(targetEntity="Application\DeskPRO\Entity\Department", mappedBy="chatQueue")
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var ArrayCollection|Department[]
     */
    private $departments;

    /**
     * @ORM\Column(name="max_queue_size", type="integer", nullable=true)
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
        $this->targets     = new ArrayCollection();
        $this->departments = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
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
     * @return int
     */
    public function getAnswerTimeout()
    {
        return $this->answerTimeout;
    }

    /**
     * @param int $answerTimeout
     *
     * @return $this
     */
    public function setAnswerTimeout($answerTimeout)
    {
        $this->setModelField('answerTimeout', $answerTimeout);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllAgents()
    {
        return $this->isAllAgents;
    }

    /**
     * @param bool $isAllAgents
     *
     * @return $this
     */
    public function setIsAllAgents($isAllAgents)
    {
        $this->setModelField('isAllAgents', $isAllAgents);

        return $this;
    }

    /**
     * @return ArrayCollection|AbstractUserChatQueueTarget[]
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @param AbstractUserChatQueueTarget $target
     *
     * @return $this
     */
    public function addTarget(AbstractUserChatQueueTarget $target)
    {
        $this->targets->add($target);
        $target->setQueue($this);

        return $this;
    }

    /**
     * @param AbstractUserChatQueueTarget $target
     *
     * @return $this
     */
    public function removeTarget(AbstractUserChatQueueTarget $target)
    {
        $this->targets->removeElement($target);
        $target->setQueue(null);

        return $this;
    }

    /**
     * @return ArrayCollection|Department[]
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function addDepartment(Department $department)
    {
        $this->departments->add($department);

        return $this;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function removeDepartment(Department $department)
    {
        $this->departments->removeElement($department);

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
