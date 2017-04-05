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

namespace DeskPRO\Bundle\UpdateBundle\BuildTasks;

use DeskPRO\Component\Util\ListUtils;

class ManifestReader
{
    /**
     * @var string
     */
    private $manifestPath;

    /**
     * @var array
     */
    private $manifest;

    /**
     * @var int[]
     */
    private $buildIds;

    /**
     * ManifestReader constructor.
     *
     * @param string $manifestPath
     */
    public function __construct($manifestPath)
    {
        $this->manifestPath = $manifestPath;
    }

    /**
     * @return string
     */
    public function getManifestPath()
    {
        return $this->manifestPath;
    }

    /**
     * @return array
     */
    public function getManifest()
    {
        if ($this->manifest === null) {
            $this->manifest = require $this->manifestPath;
            $this->buildIds = array_keys($this->manifest);
        }

        return $this->manifest;
    }

    /**
     * @param $buildId
     *
     * @return array|null
     */
    public function findBuild($buildId)
    {
        $this->getManifest();

        return isset($this->manifest[$buildId]) ? $this->manifest[$buildId] : null;
    }

    /**
     * @return int[]
     */
    public function getBuildIds()
    {
        $this->getManifest();

        return $this->buildIds;
    }

    /**
     * Gets the next build ID or 0 if the db is up to date.
     *
     * @param int $currentBuildId
     *
     * @return int
     */
    public function getNextBuildId($currentBuildId)
    {
        foreach ($this->getBuildIds() as $buildId) {
            if ($currentBuildId < $buildId) {
                return $buildId;
            }
        }

        return 0;
    }

    /**
     * Get an array of build IDs that are waiting to be performed.
     * The array is ordered.
     *
     * @param int $currentBuildId
     *
     * @return array
     */
    public function getWaitingBuildIds($currentBuildId)
    {
        $ret = [];

        foreach ($this->getBuildIds() as $buildId) {
            if ($currentBuildId < $buildId) {
                $ret[] = $buildId;
            }
        }

        return $ret;
    }

    /**
     * Get the latest build id.
     *
     * @return int
     */
    public function getLatestBuildId()
    {
        return ListUtils::last($this->getBuildIds());
    }
}
