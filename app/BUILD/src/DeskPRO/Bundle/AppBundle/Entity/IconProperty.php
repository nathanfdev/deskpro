<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class IconProperty.
 *
 * @ORM\Entity
 * @ORM\Table(name="icon_property")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @category Entities
 */
class IconProperty implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

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
     * @ORM\Column(name="options", type="json_array")
     * @Assert\NotNull()
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
     * @return IconProperty
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
        return $this->urn;
    }

    /**
     * @return string
     */
    public function getUrnPath()
    {
        return $this->urn;
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
     * @return IconProperty
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
     * @return IconProperty
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }
}
