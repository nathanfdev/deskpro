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

namespace Application\DeskPRO\ChatSetup;

use Application\DeskPRO\Settings\Settings;

class ChatSetup
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */

	private $settings;

	/**
	 * @param \Application\DeskPRO\Settings\Settings $settings
	 */

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @return array
	 */

	public function getChatInfo()
	{
		$chat_online = false;

		if (file_exists(dp_get_data_dir() . '/chat_is_available.trigger')) {

			$chat_online = file_get_contents(dp_get_data_dir() . '/chat_is_available.trigger');
			$chat_online = (bool) $chat_online;
		}

		$chat_enabled = (bool) $this->settings->get('core.apps_chat');

		return array(
			'chat_online'  => $chat_online,
			'chat_enabled' => $chat_enabled,
		);
	}

	/**
	 * @param int $is_enabled
	 */

	public function setChatEnabled($is_enabled = 1)
	{
		$this->settings->setSetting('core.apps_chat', (int) $is_enabled);
	}
}