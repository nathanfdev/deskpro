<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceAsset;

use Application\DeskPRO\Entity\Blob;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceBlobAsset.
 */
abstract class AbstractVoiceBlobAsset extends AbstractVoiceAsset
{
    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob", cascade={"persist", "remove"})
     *
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @Assert\NotBlank()
     *
     * @var Blob
     */
    protected $blob;

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }
}
