<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Server;

use Application\DeskPRO\App;
use Orb\Util\Util;

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
			$this->meminfo = @apc_sma_info();

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
	 * How much memory is currently available (free)
	 *
	 * @return int
	 */
	public function getMemFree()
	{
		return !empty($this->meminfo['avail_mem']) ? $this->meminfo['avail_mem'] : 0;
	}


	/**
	 * How much memory is available for use
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
	 * Get how much moeor
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
	 * Get the URL to the apc hitmiss chart
	 *
	 * @todo this is using App and DP_CONFIG_FILE, perhaps nicer way to do it?
	 * @return string
	 */
	public function getHitMissChartUrl()
	{
		$config_hash = md5_file(DP_CONFIG_FILE);
		$url = App::getSetting('core.deskpro_url') . '?_sys=apc&_=' . Util::generateStaticSecurityToken($config_hash.'apc', 86400) . '&IMG=1&' . time();
		return $url;
	}


	/**
	 * Get the URL to the apc memory chart
	 *
	 * @todo this is using App and DP_CONFIG_FILE, perhaps nicer way to do it?
	 * @return string
	 */
	public function getMemChartUrl()
	{
		$config_hash = md5_file(DP_CONFIG_FILE);
		$url = App::getSetting('core.deskpro_url') . '?_sys=apc&_=' . Util::generateStaticSecurityToken($config_hash.'apc', 86400) . '&IMG=1&' . time();
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