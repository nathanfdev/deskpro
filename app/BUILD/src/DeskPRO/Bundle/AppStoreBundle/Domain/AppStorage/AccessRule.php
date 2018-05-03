<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

class AccessRule
{
    /** @var string */
    private $namePattern;

    /** @var AccessOptions */
    private $accessOptions;

    /**
     * AccessRule constructor.
     * @param $namePattern
     * @param AccessOptions|null $accessOptions
     */
    public function __construct($namePattern, AccessOptions $accessOptions = null)
    {
        $this->namePattern = $namePattern;
        $this->accessOptions = $accessOptions;
    }

    /**
     * @param $name
     * @return bool
     */
    public function matchesStateName($name)
    {
        $isPrefixMatch = '*' === substr($this->namePattern, -1); // ends with "*"

        $prefixPattern = $isPrefixMatch ? substr($this->namePattern, 0, -1) : $this->namePattern;
        $prefix = $isPrefixMatch ? substr($name, 0, strlen($prefixPattern)) : $name;

        return $prefix === $prefixPattern;
    }

    /**
     * @return AccessOptions
     */
    public function getAccessOptions()
    {
        if ($this->accessOptions) {
            return $this->accessOptions;
        }

        return new AccessOptions();
    }
}
