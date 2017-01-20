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
}
