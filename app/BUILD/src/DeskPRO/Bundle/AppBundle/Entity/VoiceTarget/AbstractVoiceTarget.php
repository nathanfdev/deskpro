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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceTarget;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractVoiceTarget.
 *
 * @ORM\Entity
 * @ORM\Table(name="voice_targets")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *   "queue" = "VoiceQueueTarget",
 *   "agent" = "VoiceAgentTarget",
 *   "auto_attendant" = "VoiceAutoAttendantTarget"
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractVoiceTarget implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_QUEUE          = 'queue';
    const TYPE_AGENT          = 'agent';
    const TYPE_AUTO_ATTENDANT = 'auto_attendant';

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    abstract public function getTargetName();

    /**
     * @param string $type
     *
     * @return AbstractVoiceTarget
     */
    public static function createInstanceByType($type)
    {
        switch ($type) {
            case self::TYPE_QUEUE:
                return new VoiceQueueTarget();
            case self::TYPE_AGENT:
                return new VoiceAgentTarget();
            case self::TYPE_AUTO_ATTENDANT:
                return new VoiceAutoAttendantTarget();
            default:
                return;
        }
    }
}
