<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceQueueAgent.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_queue_agents", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="queue_agent_idx", columns={"voice_queue_id", "agent_id"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoiceQueueAgent implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceQueue", inversedBy="agents")
     * @ORM\JoinColumn(name="voice_queue_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @var VoiceQueue
     */
    protected $queue;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", inversedBy="voiceQueues")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @Assert\NotNull()
     *
     * @var Person
     */
    private $agent;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isEnabled;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     *
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->setModelField('isEnabled', $isEnabled);

        return $this;
    }
}
