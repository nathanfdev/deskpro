<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceAsset;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceRecordAsset.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity
 */
class VoiceRecordAsset extends AbstractVoiceBlobAsset
{
    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name = '';

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return AbstractVoiceAsset::TYPE_RECORD;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }
}
