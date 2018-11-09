<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class AppVersion
{
    /** @var string */
    private $major;

    /** @var string */
    private $minor;

    /** @var string */
    private $patch;

    /** @var string */
    private $label;

    /**
     * @param string $version
     * @return AppVersion
     */
    static public function parse($version)
    {
        $pattern = "/^v?(0|[1-9]\d*)\\.(0|[1-9]\d*)\\.(0|[1-9]\d*)(-.+)?$/";
        $matches = [];
        $result = preg_match($pattern, $version, $matches);

        if ($result !== 1) {
            throw new \DomainException("the string: $version doesn't look like a semver number");
        }

        if (count($matches) < 3) {
            throw new \DomainException("the string: $version doesn't look like a semver number");
        }

        list($matched, $major, $minor, $patch) = $matches;

        if (count($matches) == 5) {
            $label = substr($matches[4], 1);
        } else {
            $label = "";
        }

        return new AppVersion($major, $minor, $patch, $label);
    }

    /**
     * AppVersion constructor.
     * @param string $major
     * @param string $minor
     * @param string $patch
     * @param string $label
     */
    public function __construct( $major, $minor, $patch, $label = "" )
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * @return string
     */
    public function getMinor()
    {
        return $this->minor;
    }

    /**
     * @return string
     */
    public function getPatch()
    {
        return $this->patch;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }


}
