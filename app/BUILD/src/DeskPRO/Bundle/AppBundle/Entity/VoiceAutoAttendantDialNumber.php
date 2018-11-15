<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\VoiceBundle\Validator\Constraints as VoiceAssert;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAutoAttendantDialNumber.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_auto_attendant_dial_numbers", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="dial_nums_unique_idx", columns={"voice_auto_attendant_id", "dial_num"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity(fields={"voiceAutoAttendant", "dialNum"}, errorPath="dialNum")
 * @VoiceAssert\VoiceAutoAttendantTargetSelf()
 */
class VoiceAutoAttendantDialNumber implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant", inversedBy="dialNumbers")
     * @ORM\JoinColumn(name="voice_auto_attendant_id")
     *
     * @var VoiceAutoAttendant
     */
    private $voiceAutoAttendant;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     *
     * @Assert\Valid()
     *
     * @var AbstractVoiceTarget
     */
    private $target;

    /**
     * @ORM\Column(name="dial_num", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @Assert\NotNull()
     * @Assert\Range(min="1", max="9")
     *
     * @var int
     */
    private $dialNum;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoiceAutoAttendant
     */
    public function getVoiceAutoAttendant()
    {
        return $this->voiceAutoAttendant;
    }

    /**
     * @param VoiceAutoAttendant $voiceAutoAttendant
     *
     * @return $this
     */
    public function setVoiceAutoAttendant(VoiceAutoAttendant $voiceAutoAttendant = null)
    {
        $this->setModelField('voiceAutoAttendant', $voiceAutoAttendant);

        return $this;
    }

    /**
     * @return AbstractVoiceTarget
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param AbstractVoiceTarget $target
     *
     * @return $this
     */
    public function setTarget(AbstractVoiceTarget $target = null)
    {
        $this->setModelField('target', $target);

        return $this;
    }

    /**
     * @return int
     */
    public function getDialNum()
    {
        return $this->dialNum;
    }

    /**
     * @param int $dialNum
     *
     * @return $this
     */
    public function setDialNum($dialNum)
    {
        $this->setModelField('dialNum', $dialNum);

        return $this;
    }
}
