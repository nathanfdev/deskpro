<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoiceRecording.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_recordings")
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoiceRecording extends AbstractVoiceRecording
{
    use NotifyPropertyChangedTrait;
}
