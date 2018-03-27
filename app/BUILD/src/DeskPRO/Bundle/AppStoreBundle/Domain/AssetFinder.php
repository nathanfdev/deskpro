<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface AssetFinder
{
    /**
     * @param Application $application
     * @param SearchAssetFilter $assetFilter
     * @return  []
     */
    function findApplicationAssets(Application $application, SearchAssetFilter $assetFilter);
}

