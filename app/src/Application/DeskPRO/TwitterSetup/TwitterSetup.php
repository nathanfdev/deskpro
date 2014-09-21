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
 */

namespace Application\DeskPRO\TwitterSetup;

class TwitterSetup
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */

	private $settings;

	/** @var string */
	public $twitter_agent_consumer_key = '';
	/** @var string */
	public $twitter_agent_consumer_secret = '';
	/** @var string */
	public $twitter_user_consumer_key = '';
	/** @var string */
	public $twitter_user_consumer_secret = '';
	/** @var int */
	public $twitter_auto_remove_time = 1209600;

	/**
	 * @param \Application\DeskPRO\Settings\Settings $settings
	 */

	public function __construct(\Application\DeskPRO\Settings\Settings $settings)
	{
		$this->settings = $settings;
		$this->resetTwitterSetup();
	}

	/**
	 * Resets twitter setup based on stored values.
	 */

	public function resetTwitterSetup()
	{
		$this->twitter_agent_consumer_key    = $this->settings->get('core.twitter_agent_consumer_key');
		$this->twitter_agent_consumer_secret = $this->settings->get('core.twitter_agent_consumer_secret');
		$this->twitter_user_consumer_key     = $this->settings->get('core.twitter_user_consumer_key');
		$this->twitter_user_consumer_secret  = $this->settings->get('core.twitter_user_consumer_secret');
		$this->twitter_auto_remove_time      = $this->settings->get('core.twitter_auto_remove_time');
	}

	/**
	 * @return array
	 */

	public function toArray()
	{
		$export_settings = array();

		foreach (
			array(
				'twitter_agent_consumer_key',
				'twitter_agent_consumer_secret',
				'twitter_user_consumer_key',
				'twitter_user_consumer_secret',
				'twitter_auto_remove_time',
			) as $s) {

			$export_settings[$s] = $this->$s;
		}

		return $export_settings;
	}

	/**
	 * @param array $new_values
	 */

	public function setArray(array $new_values)
	{
		foreach ($new_values as $v => $val) {
			if (property_exists($this, $v)) {
				$this->$v = $val;
			}
		}
	}

	/**
	 * Persists twitter setup
	 */

	public function saveTwitterSetup()
	{
		$this->settings->setSetting('core.twitter_agent_consumer_key', $this->twitter_agent_consumer_key);
		$this->settings->setSetting('core.twitter_agent_consumer_secret', $this->twitter_agent_consumer_secret);
		$this->settings->setSetting('core.twitter_user_consumer_key', $this->twitter_user_consumer_key);
		$this->settings->setSetting('core.twitter_user_consumer_secret', $this->twitter_user_consumer_secret);
		$this->settings->setSetting('core.twitter_auto_remove_time', (int) $this->twitter_auto_remove_time);
	}
}