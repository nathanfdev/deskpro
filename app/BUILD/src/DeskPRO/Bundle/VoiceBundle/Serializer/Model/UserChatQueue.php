<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue as UserChatQueueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class UserChatQueue.
 */
class UserChatQueue
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
     * @JMS\Type("string")
     *
     * @var string
     */
    private $routingModel;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var int
     */
    private $answerTimeout;

    /**
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isAllAgents;

    /**
     * @JMS\Expose()
     * @JMS\Type("collection<DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget>")
     *
     * @var AbstractUserChatQueueTarget[]|ArrayCollection
     */
    private $targets;

    /**
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var ArrayCollection|Department[]
     */
    private $departments;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $maxQueueSize;

    /**
     * Constructor.
     *
     * @param UserChatQueueEntity $entity
     */
    public function __construct(UserChatQueueEntity $entity)
    {
        $this->id            = $entity->getId();
        $this->name          = $entity->getName();
        $this->routingModel  = $entity->getRoutingModel();
        $this->answerTimeout = $entity->getAnswerTimeout();
        $this->isAllAgents   = $entity->isAllAgents();
        $this->targets       = $entity->getTargets();
        $this->departments   = $entity->getDepartments();
        $this->maxQueueSize  = $entity->getMaxQueueSize();
    }

    /**
     * @param $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
    }
}
