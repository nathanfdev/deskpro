<?php

namespace DeskPRO\Bundle\AppBundle\Assets;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

/**
 * Class DpStaticVersionStrategy.
 */
class DpStaticVersionStrategy implements VersionStrategyInterface
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $format;

    /**
     * Constructor.
     *
     * @param string $version Version number
     * @param string $format  Url format
     */
    public function __construct($version, $format = null)
    {
        $this->version = $version;
        $this->format  = $format ?: '%s?v=%s';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($path)
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function applyVersion($path)
    {
        // don't add version cache boost to asset paths w/o extension
        if (!preg_match('#.\w+$#', $path)) {
            return $path;
        }

        $versionized = sprintf($this->format, ltrim($path, '/'), $this->getVersion($path));

        if ($path && '/' == $path[0]) {
            return '/'.$versionized;
        }

        return $versionized;
    }
}
