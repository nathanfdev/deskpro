<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Server;

use Application\DeskPRO\App;
use Orb\Util\Util;

// TODO: This is old, check to see if it can be completely removed.
class ApcStatus
{
    /**
     * @var bool
     */
    private $is_enabled;

    /**
     * @var array
     */
    private $cacheinfo;

    /**
     * @var array
     */
    private $meminfo;

    public function __construct()
    {
        $this->is_enabled = function_exists('apc_cache_info');

        if ($this->is_enabled) {
            $this->cacheinfo = @apc_cache_info('opcode');
            $this->meminfo   = @apc_sma_info();

            if (!$this->cacheinfo || !$this->meminfo) {
                $this->is_enabled = false;

                return;
            }
        }
    }

    /**
     * @return int
     */
    public function getNumMisses()
    {
        return !empty($this->cacheinfo['num_misses']) ? $this->cacheinfo['num_misses'] : 0;
    }

    /**
     * @return int
     */
    public function getNumHits()
    {
        return !empty($this->cacheinfo['num_hits']) ? $this->cacheinfo['num_hits'] : 0;
    }

    /**
     * @return int
     */
    public function getNumTotalReqs()
    {
        return $this->getNumMisses() + $this->getNumHits();
    }

    /**
     * @return float
     */
    public function getMissPercent()
    {
        $miss  = $this->getNumMisses();
        $total = $this->getNumTotalReqs();

        if (!$total) {
            return 0.0;
        }

        return ($miss / $total) * 100;
    }

    /**
     * @return float
     */
    public function getHitPercent()
    {
        $hit   = $this->getNumHits();
        $total = $this->getNumTotalReqs();

        if (!$total) {
            return 0.0;
        }

        return ($hit / $total) * 100;
    }

    /**
     * How much memory is currently available (free).
     *
     * @return int
     */
    public function getMemFree()
    {
        return !empty($this->meminfo['avail_mem']) ? $this->meminfo['avail_mem'] : 0;
    }

    /**
     * How much memory is available for use.
     *
     * @return int
     */
    public function getMemTotal()
    {
        if (!empty($this->meminfo['num_seg']) && !empty($this->meminfo['seg_size'])) {
            return $this->meminfo['num_seg'] * $this->meminfo['seg_size'];
        }

        return 0;
    }

    /**
     * Get how much moeor.
     *
     * @return int
     */
    public function getMemUsed()
    {
        return $this->getMemTotal() - $this->getMemFree();
    }

    /**
     * @return float
     */
    public function getMemUsedPercent()
    {
        $total = $this->getMemTotal();
        $used  = $this->getMemUsed();

        if (!$total) {
            return 0.0;
        }

        return ($used / $total) * 100;
    }

    /**
     * @return float
     */
    public function getMemFreePercent()
    {
        $total = $this->getMemTotal();
        $free  = $this->getMemFree();

        if (!$total) {
            return 0.0;
        }

        return ($free / $total) * 100;
    }

    /**
     * Get the URL to the apc hitmiss chart.
     *
     * @todo this is using App and DP_CONFIG_FILE, perhaps nicer way to do it?
     *
     * @return string
     */
    public function getHitMissChartUrl()
    {
        $config_hash = '';
        $url         = App::getContainer()->getBrandSetting('core.deskpro_url').'?_sys=apc&_='
            .Util::generateStaticSecurityToken($config_hash.'apc', 86400).'&IMG=1&'.time();

        return $url;
    }

    /**
     * Get the URL to the apc memory chart.
     *
     * @todo this is using App and DP_CONFIG_FILE, perhaps nicer way to do it?
     *
     * @return string
     */
    public function getMemChartUrl()
    {
        $config_hash = '';
        $url         = App::getContainer()->getBrandSetting('core.deskpro_url').'?_sys=apc&_='
            .Util::generateStaticSecurityToken($config_hash.'apc', 86400).'&IMG=1&'.time();

        return $url;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * Guess if there is a problem based on the number of misses we've had.
     *
     * @return bool
     */
    public function guessIsProblem()
    {
        if (!$this->is_enabled) {
            return false;
        }

        if ($this->getMissPercent() > 30 && $this->getNumTotalReqs() > 20) {
            return true;
        }

        return false;
    }
}
