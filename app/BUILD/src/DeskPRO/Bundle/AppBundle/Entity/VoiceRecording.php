<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoiceRecording.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_recordings", indexes={@ORM\Index(name="recording_sid", columns={"recording_sid"})})
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoiceRecording extends AbstractVoiceRecording
{
    use NotifyPropertyChangedTrait;
}
