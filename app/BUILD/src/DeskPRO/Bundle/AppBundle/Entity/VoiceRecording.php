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

    /**
     * Additional data attached to message (not implemented).
     *
     * @ORM\Column(type="json_array", nullable=false)
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * @param VoiceRecording $recording
     */
    public function addVoiceRecordingMetadata(VoiceRecording $recording)
    {
        $metadata = $this->metadata;
        if (!isset($this->metadata['recordings_count'])) {
            $metadata['recordings_count'] = 0;
        }
        $metadata[] = [
            'duration' => $recording->getDuration(),
            'sid'      => $recording->getRecordingSid(),
            'url'      => $recording->getRecordingUrl(),
        ];
        $metadata['recordings_count'] += 1;
        $this->setModelField('metadata', $metadata);
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
