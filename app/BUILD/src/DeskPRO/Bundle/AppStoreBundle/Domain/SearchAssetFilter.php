<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class SearchAssetFilter
{
    /** @var string */
    private $fileExtension;

    /** @var string */
    private $pathPattern;

    /** @var string */
    private $pathMatchingStrategy = 'prefix';

    /**
     * SearchAssetFilter constructor.
     * @param string|null $fileExtension
     * @param string|null $pathPattern
     * @param bool $usePrefixPathMatchingStrategy
     */
    public function __construct($fileExtension = null, $pathPattern = null, $usePrefixPathMatchingStrategy = false)
    {
        $this->fileExtension = $fileExtension;
        $this->pathPattern = $pathPattern;

        if (! $usePrefixPathMatchingStrategy) {
            $this->pathMatchingStrategy = 'exact';
        }
    }

    /**
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * @return string
     */
    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    /**
     * @return bool
     */
    public function usePrefixPathMatchingStrategy()
    {
        return $this->pathMatchingStrategy == 'prefix';
    }
}
