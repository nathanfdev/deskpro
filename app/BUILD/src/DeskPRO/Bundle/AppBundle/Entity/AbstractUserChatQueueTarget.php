<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractUserChatQueueTarget.
 *
 * @ORM\Entity()
 * @ORM\Table(name="user_chat_queue_targets")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *   "agent" = "UserChatQueueAgent"
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractUserChatQueueTarget implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_AGENT = 'agent';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\UserChatQueue", inversedBy="targets")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @var UserChatQueue
     */
    protected $queue;

    /**
     * @ORM\Column(name="sort", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $sort = 10;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return UserChatQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param UserChatQueue $queue
     *
     * @return $this
     */
    public function setQueue(UserChatQueue $queue = null)
    {
        $this->setModelField('queue', $queue);

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return $this
     */
    public function setSort($sort)
    {
        $this->setModelField('sort', $sort);

        return $this;
    }

    /**
     * @param string $type
     *
     * @return AbstractUserChatQueueTarget
     */
    public static function createInstanceByType($type)
    {
        switch ($type) {
            case self::TYPE_AGENT:
                return new UserChatQueueAgent();
            default:
                return;
        }
    }

    /**
     * @return array
     */
    abstract public function toArray();
}
