<?php

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
