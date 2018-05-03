<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceTarget;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceQueueTarget.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity
 */
class VoiceQueueTarget extends AbstractVoiceTarget
{
    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceQueue")
     * @ORM\JoinColumn(name="voice_queue_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceQueue>")
     * @JMS\SerializedName("target")
     *
     * @Assert\NotNull()
     *
     * @var VoiceQueue
     */
    protected $queue;

    /**
     * @return VoiceQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param VoiceQueue $queue
     *
     * @return $this
     */
    public function setQueue(VoiceQueue $queue = null)
    {
        $this->setModelField('queue', $queue);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetName()
    {
        return $this->queue->getName();
    }
}
