<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\CacheVersionRepository")
 * @ORM\Table(name="cache_versions")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class CacheVersion implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\Column(type="string", length=150)
     */
    protected $resource_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $version_id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return $this->resource_id;
    }

    /**
     * @param string $resource_id
     *
     * @return $this
     */
    public function setResourceId($resource_id)
    {
        $this->setModelField('resource_id', $resource_id);

        return $this;
    }

    /**
     * @return string
     */
    public function getVersionId()
    {
        return $this->version_id;
    }

    /**
     * @param string $version_id
     *
     * @return $this
     */
    public function setVersionId($version_id)
    {
        $this->setModelField('version_id', $version_id);

        return $this;
    }
}
