<?php

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
    protected $id;

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
