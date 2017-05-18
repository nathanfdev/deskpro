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
