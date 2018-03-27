<?php

namespace DeskPRO\Bundle\AppBundle\Entity\AppStore;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app2_app_asset_blob")
 *
 * @JMS\ExclusionPolicy("all")
 */
class AppAssetBlob implements Domain\ApplicationAsset, EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /** @var DeskproBlobStorage */
    private static $blobStorageService;

    /**
     * @return DeskproBlobStorage
     */
    public static function getBlobStorageService()
    {
        return self::$blobStorageService;
    }

    /**
     * @param DeskproBlobStorage $blobStorageService
     */
    public static function setBlobStorageService(DeskproBlobStorage $blobStorageService)
    {
        self::$blobStorageService = $blobStorageService;
    }

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
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\App")
     * @ORM\JoinColumn(name="app_id", referencedColumnName="id", nullable = false, onDelete="CASCADE")
     *
     * @var App
     */
    private $app;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $path;

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id")
     *
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var Blob
     */
    private $blob;

    /**
     * @ORM\Column(name="blob_authcode", type="string", nullable=false)
     */
    private $blobAuthcode;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param App $app
     */
    public function setApp(App $app = null)
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function copy($newPath)
    {
        if ($this->blob) {
            $blobStorage = self::getBlobStorageService();
            $blob        = $blobStorage->createBlobRecordFromString(
                $blobStorage->copyBlobRecordToString($this->blob),
                $newPath,
                $this->blob->content_type
            );
            $assetBlob = new self();
            $assetBlob->setBlob($blob);
            $assetBlob->setPath($newPath);

            return $assetBlob;
        }

        $assetBlob = new self();
        $assetBlob->setPath($newPath);

        return $assetBlob;
    }

    /**
     * @param Blob $blob
     *
     * @return AppAssetBlob
     */
    public function setBlob(Blob $blob)
    {
        $this->blob         = $blob;
        $this->blobAuthcode = $blob->getAuthcode();

        return $this;
    }

    /**
     * Returns the system identifier for the application.
     *
     * @return string
     */
    public function getBlobId()
    {
        return $this->blob ? $this->blob->getId() : null;
    }

    /**
     * @return string
     */
    public function getRawContent()
    {
        if ($this->blob) {
            $blobStorage = self::getBlobStorageService();

            return $blobStorage->copyBlobRecordToString($this->blob);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        if ($this->blob) {
            return $this->blob->content_type;
        }

        return null;
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }
}
