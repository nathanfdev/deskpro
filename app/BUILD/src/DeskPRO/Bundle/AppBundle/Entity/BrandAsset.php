<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="brand_assets")
 * @Serializer\ExclusionPolicy("ALL")
 */
class BrandAsset implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @Serializer\Expose()
     */
    protected $id = null;
    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     * @Assert\NotNull()
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(type="simple_array")
     * @Serializer\Expose()
     * @Assert\NotNull()
     */
    protected $tags = [];

    /**
     * @var \Application\DeskPRO\Entity\Brand
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Brand")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $brand;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob", fetch="EAGER")
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $blob;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime")
     * @Serializer\Expose()
     */
    protected $date_created;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_updated", type="datetime")
     * @Serializer\Expose()
     */
    protected $date_updated;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return $this
     */
    public function setTags(array $tags)
    {
        $this->setModelField('tags', $tags);

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param \Application\DeskPRO\Entity\Brand $brand
     *
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param \Application\DeskPRO\Entity\Blob $blob
     *
     * @return $this
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param \DateTime $date_updated
     *
     * @return $this
     */
    public function setDateUpdated($date_updated)
    {
        $this->setModelField('date_updated', $date_updated);

        return $this;
    }
}
