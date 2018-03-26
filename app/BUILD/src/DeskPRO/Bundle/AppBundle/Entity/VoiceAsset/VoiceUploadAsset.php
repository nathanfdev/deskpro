<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceAsset;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoiceUploadAsset.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity()
 */
class VoiceUploadAsset extends AbstractVoiceBlobAsset
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return AbstractVoiceAsset::TYPE_UPLOAD;
    }
}
