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

namespace DeskPRO\Bundle\AppBundle\Entity\AppStore;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppManifestReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app2_app", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="name_unique", columns={"name", "is_dev"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
class App implements Domain\Application, EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @ORM\Column(name="`is_dev`", type="boolean", options={"default" = 0}, nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    private $isDev = false;

    /**
     * @ORM\Column(name="`manifest`", type="json_array", nullable=false)
     * @JMS\Expose()
     *
     * @return array
     */
    private $manifest;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\AppAssetBlob", mappedBy="app", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @JMS\Expose()
     * @JMS\Type("collection<DeskPRO\Bundle\AppBundle\Entity\AppStore\AppAssetBlob>")
     *
     * @var AppAssetBlob[]
     */
    private $assets;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance", mappedBy="app", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var AppInstance[]
     */
    private $instances;

    /**
     * @var Domain\AppManifest
     */
    private $parsedManifest;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->assets    = new ArrayCollection();
        $this->instances = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the manifest.
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest")
     *
     * @return Domain\AppManifest
     */
    public function getManifest()
    {
        if ($this->manifest) {
            $manifestReader = new AppManifestReader();
            return $manifestReader->readManifestFromArray($this->manifest);
        }

        return null;
    }

    /**
     * @return Domain\AppManifest
     * @deprecated
     */
    public function getParsedManifest()
    {
        return $this->getManifest();
    }

    /**
     * @param array $manifest
     *
     * @return $this
     */
    public function setManifest(array $manifest)
    {
        $this->setModelField('manifest', $manifest);

        return $this;
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
     * @param AppAssetBlob $asset
     *
     * @return $this
     */
    public function addAsset(AppAssetBlob $asset)
    {
        $this->assets->add($asset);
        $asset->setApp($this);

        return $this;
    }

    /**
     * @return AppAssetBlob[]|ArrayCollection
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @return AppAssetBlob
     */
    public function getIconAsset()
    {
        return $this->assets->filter(function (AppAssetBlob $asset) {
            return $asset->getPath() === 'assets/icon.png';
        })->first();
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return null|string
     */
    public function getIconUrl()
    {
        $asset = $this->getIconAsset();

        return $asset ? $asset->getBlob()->getThumbnailUrl('{{size}}') : null;
    }

    /**
     * @return ArrayCollection|AppInstance[]
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * @return boolean
     */
    public function getIsDev()
    {
        return $this->isDev;
    }

    /**
     * @param boolean $isDev
     */
    public function setIsDev( $isDev )
    {
        $this->isDev = $isDev;
    }
}
