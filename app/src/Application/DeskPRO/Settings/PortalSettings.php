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

class PortalSettings
{
	/**
	 * @var Settings
	 */
	private $settings;

	/** @var bool */
	public $portal_enabled;
	/** @var string */
	public $favicon_blob_url;
	/** @var int */
	public $favicon_blob_id;

	/** @var int */
	public $show_ratings;
	/** @var bool */
	public $publish_comments;

	/** @var bool */
	public $register_captcha;
	/** @var bool */
	public $publish_captcha;
	/** @var bool */
	public $always_show_captcha;

	/** @var bool */
	public $feedback_notify_comments;
	/** @var bool */
	public $kb_subscriptions;

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
		$this->portal_enabled   = (bool)$this->settings->get('user.portal_enabled');

		$this->favicon_blob_id  = (int)$this->settings->get('core.favicon_blob_id');
		$this->favicon_blob_url = $this->settings->get('core.favicon_blob_url') ?: null;

		$this->show_ratings     = (int)$this->settings->get('user.show_ratings');
		$this->publish_comments = (bool)$this->settings->get('user.publish_comments');

		$this->register_captcha    = (bool)$this->settings->get('user.register_captcha');
		$this->publish_captcha     = (bool)$this->settings->get('user.publish_captcha');
		$this->always_show_captcha = (bool)$this->settings->get('user.always_show_captcha');

		$this->feedback_notify_comments = (bool)$this->settings->get('user.feedback_notify_comments');
		$this->kb_subscriptions         = (bool)$this->settings->get('user.kb_subscriptions');
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$export_settings = array(
			'portal_enabled'           => $this->portal_enabled,
			'favicon_blob_id'          => $this->favicon_blob_id,
			'favicon_blob_url'         => $this->favicon_blob_url,
			'show_ratings'             => $this->show_ratings,
			'publish_comments'         => $this->publish_comments,
			'register_captcha'         => $this->register_captcha,
			'publish_captcha'          => $this->publish_captcha,
			'always_show_captcha'      => $this->always_show_captcha,
			'feedback_notify_comments' => $this->feedback_notify_comments,
			'kb_subscriptions'         => $this->kb_subscriptions,
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
		$this->settings->setSetting("user.show_ratings", (int)$this->show_ratings);

		if ($this->favicon_blob_id && $this->favicon_blob_url) {
			$this->settings->setSetting('core.favicon_blob_id', (int)$this->favicon_blob_id);
			$this->settings->setSetting('core.favicon_blob_url', (int)$this->favicon_blob_url);
		} else {
			$this->settings->setSetting('core.favicon_blob_id', null);
			$this->settings->setSetting('core.favicon_blob_url', null);
		}

		foreach (array(
			'publish_comments', 'register_captcha', 'publish_captcha',
			'always_show_captcha', 'feedback_notify_comments', 'kb_subscriptions'
		) as $p) {
			$this->settings->setSetting("user.$p", (bool)$this->$p);
		}
	}
}