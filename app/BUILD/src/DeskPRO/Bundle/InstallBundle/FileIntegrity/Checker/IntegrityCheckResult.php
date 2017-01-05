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

namespace DeskPRO\Bundle\InstallBundle\FileIntegrity\Checker;

class IntegrityCheckResult
{
    const MISSING = 'missing';
    const INVALID = 'invalid';

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var int
     */
    private $count_bad = 0;

    /**
     * @var int
     */
    private $count_okay;

    /**
     * @var array
     */
    private $bad_paths = [];

    /**
     * @var array
     */
    private $okay_paths = [];

    /**
     * @param $p
     * @param $reason
     */
    public function recordBadPath($p, $reason)
    {
        ++$this->count;
        ++$this->count_bad;
        $this->bad_paths[] = ['path' => $p, 'error' => $reason];
    }

    /**
     * @param string $p
     */
    public function recordOkayPath($p)
    {
        ++$this->count;
        ++$this->count_okay;
        $this->okay_paths[] = $p;
    }

    /**
     * @return int
     */
    public function countAll()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function countBad()
    {
        return $this->count_bad;
    }

    /**
     * Get's an array of ['path' => 'xxx', 'error' => 'reason code'].
     *
     * @return array
     */
    public function getBadPaths()
    {
        return array_map(function ($v) {
            return $v['path'];
        }, $this->bad_paths);
    }

    /**
     * @return array
     */
    public function getBadInfo()
    {
        return $this->bad_paths;
    }

    /**
     * @return array
     */
    public function getOkayPaths()
    {
        return $this->okay_paths;
    }
}
