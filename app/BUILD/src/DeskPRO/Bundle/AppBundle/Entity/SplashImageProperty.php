<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SplashImageProperty.
 *
 * @ORM\Entity
 * @ORM\Table(name="splash_image_property")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("ALL")
 *
 * @category Entities
 */
class SplashImageProperty implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    public static $blobNs     = 'urn:deskpro:local:blobs';
    public static $unsplashNs = 'urn:deskpro:product:splash:unsplash';

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="urn", type="string")
     * @Assert\NotNull()
     */
    protected $urn;

    /**
     * @var array
     * @ORM\Column(name="options", type="json_array", nullable=true)
     */
    protected $options;

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob", fetch="EAGER")
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Blob
     */
    protected $blob;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrn()
    {
        return $this->urn;
    }

    /**
     * @param string $urn
     *
     * @return SplashImageProperty
     */
    public function setUrn($urn)
    {
        $this->setModelField('urn', $urn);

        return $this;
    }

    /**
     * @return string
     */
    public function getUrnNs()
    {
        return implode(':', array_slice(explode(':', $this->urn), 0, -1));
    }

    /**
     * @return string
     */
    public function getUrnPath()
    {
        $parts = explode(':', $this->urn);

        return $parts[count($parts) - 1];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return SplashImageProperty
     */
    public function setOptions($options)
    {
        $this->setModelField('options', $options);

        return $this;
    }

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
     * @return SplashImageProperty
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }
}
