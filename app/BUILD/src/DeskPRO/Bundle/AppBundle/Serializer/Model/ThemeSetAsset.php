<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset as ThemeSetAssetEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ThemeSetAsset.
 */
class ThemeSetAsset
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Theme set asset name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * Theme set for this asset.
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\ThemeSet>")
     *
     * @var \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    private $themeSet;

    /**
     * a URL for user custom assets uploaded in the Portal Designer.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url;

    /**
     * The id of the blob linked to the asset.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $blobId;

    /**
     * The mimeType of the asset.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $mimeType;

    /**
     * ThemeSetAsset constructor.
     *
     * @param ThemeSetAssetEntity $asset
     * @param string              $url
     * @param $blobAuthId
     */
    public function __construct(ThemeSetAssetEntity $asset, $url, $blobAuthId)
    {
        $this->id       = $asset->getId();
        $this->name     = $asset->getName();
        $this->themeSet = $asset->getThemeSet();
        $this->mimeType = $asset->getBlob()->getContentType();
        $this->url      = $url;
        $this->blobId   = $blobAuthId;
    }
}
