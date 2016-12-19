<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\ThemeSetAssetRepository")
 * @ORM\Table(name="theme_set_assets")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class ThemeSetAsset implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id = null;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotNull()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="simple_array")
     *
     * @Assert\NotNull()
     *
     * @var array
     */
    protected $tags = [];

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\ThemeSet", inversedBy="assets")
     * @ORM\JoinColumn(name="theme_set_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    protected $theme_set;

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob", fetch="EAGER")
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @ORM\Column(name="date_updated", type="datetime")
     *
     * @var \DateTime
     */
    protected $date_updated;

    /**
     * ThemeSetAsset constructor.
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_updated', new \DateTime());
    }

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
     * @return ThemeSet
     */
    public function getThemeSet()
    {
        return $this->theme_set;
    }

    /**
     * @param ThemeSet $theme_set
     *
     * @return $this
     */
    public function setThemeSet(ThemeSet $theme_set)
    {
        $this->setModelField('theme_set', $theme_set);

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
