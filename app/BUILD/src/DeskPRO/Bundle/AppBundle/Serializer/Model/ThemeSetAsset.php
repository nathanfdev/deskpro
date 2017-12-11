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
        $this->url      = $url;
        $this->blobId   = $blobAuthId;
    }
}
