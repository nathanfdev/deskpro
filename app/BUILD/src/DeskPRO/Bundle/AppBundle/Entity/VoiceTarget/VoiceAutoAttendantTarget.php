<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceTarget;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAutoAttendantTarget.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity
 */
class VoiceAutoAttendantTarget extends AbstractVoiceTarget
{
    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant")
     * @ORM\JoinColumn(name="voice_auto_attendant_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant>")
     * @JMS\SerializedName("target")
     *
     * @Assert\NotNull()
     *
     * @var VoiceAutoAttendant
     */
    protected $autoAttendant;

    /**
     * @return VoiceAutoAttendant
     */
    public function getAutoAttendant()
    {
        return $this->autoAttendant;
    }

    /**
     * @param VoiceAutoAttendant $autoAttendant
     *
     * @return $this
     */
    public function setAutoAttendant(VoiceAutoAttendant $autoAttendant = null)
    {
        $this->setModelField('autoAttendant', $autoAttendant);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetName()
    {
        return $this->autoAttendant->getName();
    }
}
