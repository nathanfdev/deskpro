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

namespace Application\DeskPRO\Settings;
use Orb\Util\Numbers;
use Orb\Util\Util;

class ServerSettings
{
	/**
	 * @var Settings
	 */
	private $settings;

	public $rewrite_urls = false;
	public $redirect_correct_url = true;
	public $cookie_path = '/';
	public $cookie_domain = '';

	/**
	 * @param Settings $settings
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
		$this->resetSettings();
	}


	/**
	 * Resets settings based on stored values.
	 */
	public function resetSettings()
	{
		$this->rewrite_urls         = (bool)$this->settings->get('core.rewrite_urls');
		$this->redirect_correct_url = (bool)$this->settings->get('core.redirect_correct_url');

		$this->cookie_path = $this->settings->get('core.cookie_path');
		if ($this->cookie_path === null) {
			$this->cookie_path = '/';
		}

		$this->cookie_domain = $this->settings->get('core.cookie_domain');
		if ($this->cookie_domain === null) {
			$this->cookie_domain = '';
		}
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$export_settings = array(
			'rewrite_urls'                    => $this->rewrite_urls,
			'redirect_correct_url'            => $this->redirect_correct_url,
			'cookie_path'                     => $this->cookie_path,
			'cookie_domain'                   => $this->cookie_domain,
		);
		return $export_settings;
	}


	/**
	 * @param array $set_settings
	 */
	public function setArray(array $set_settings)
	{
		foreach ($set_settings as $s => $val) {
			if (property_exists($this, $s)) {
				$this->$s = $val;
			}
		}
	}


	/**
	 * Persists settings
	 */
	public function saveSettings()
	{
		$this->settings->setSetting('core.rewrite_urls', Util::boolInt($this->rewrite_urls));
		$this->settings->setSetting('core.redirect_correct_url', Util::boolInt($this->redirect_correct_url));
		$this->settings->setSetting('core.cookie_path', $this->cookie_path);
		$this->settings->setSetting('core.cookie_domain', $this->cookie_domain);
	}
}