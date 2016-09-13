<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);
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
     */
    public function setTags(array $tags)
    {
        $this->setModelField('tags', $tags);
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
     */
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);
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
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);
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
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);
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
     */
    public function setDateUpdated($date_updated)
    {
        $this->setModelField('date_updated', $date_updated);
    }
}
