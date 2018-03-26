<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class SearchFilters
{
    /**
     * @param SearchAssetFilterOptions $valueMap
     * @return SearchAssetFilter
     */
    public function convertValueMapToAssetFilter(SearchAssetFilterOptions $valueMap)
    {
        $fileExtension = $valueMap->getFileExtension();
        if ("" == $fileExtension || is_null($fileExtension) || false == (bool) preg_match('#[^/]+#', $fileExtension)) {
            $fileExtension = null;
        }

        $pathPattern = $valueMap->getFilePathPattern();
        if ("" == $pathPattern || is_null($pathPattern)) {
            $pathPattern = null;
        }

        $usePrefixPathMatchingStrategy = "/" == substr($pathPattern, -1);
        return new SearchAssetFilter($fileExtension, $pathPattern, $usePrefixPathMatchingStrategy);
    }

    public function convertValueMapToStateFilter( SearchAppStorageFilterOptions $valueMap)
    {
        $name = $valueMap->getStateVariableName();
        $entityId = AppStorage\EntityId::parse($valueMap->getEntityId());
        return new AppStorageSearchFilter($valueMap->getApplicationId(), $entityId, $name);
    }

}
