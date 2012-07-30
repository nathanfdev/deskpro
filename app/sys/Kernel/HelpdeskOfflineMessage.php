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

namespace DeskPRO\Kernel;

class HelpdeskOfflineMessage
{
	public static function getOfflinePage($message = null)
	{
		$page_html = file_get_contents(DP_ROOT . '/src/Application/DeskPRO/Resources/views/helpdesk-disabled.html');

		if ($message === null) {
			$page_html = str_replace('{{ OFFLINE_MESSAGE }}', self::getOfflineMessage(), $page_html);
		} else {
			$page_html = str_replace('{{ OFFLINE_MESSAGE }}', $message, $page_html);
		}

		return $page_html;
	}

	public static function getLicenseErrorPage($type, $base_url)
	{
		switch ($type) {
			case 'agents':
				if (DP_INTERFACE == 'user') $message = 'Our helpdesk is currently offline for maintenance. (L01)';
				else $message = 'You have more agents than your license allows.';
				break;

			case 'expired':
				if (DP_INTERFACE == 'user') $message = 'Our helpdesk is currently offline for maintenance. (L02)';
				else $message = 'Your license has expired.';
				break;

			default: trigger_error('getLicenseErrorPage called with bad $type', E_USER_ERROR); return '';
		}

		if (DP_INTERFACE == 'user') {
			$page_html = self::getOfflinePage($message);
		} else {
			$page_html = file_get_contents(DP_ROOT . '/src/Application/DeskPRO/Resources/views/license-error.html');
			$page_html = str_replace('{{ LICENSE_MESSAGE }}', $message, $page_html);
			$page_html = str_replace('{{ BILLING_URL }}', $base_url . '/billing/', $page_html);
		}

		return $page_html;
	}

	public static function getOfflineMessage()
	{
		$offline_message = null;
		if (file_exists(dp_get_tmp_dir() . '/helpdesk-offline-message.txt')) {
			$offline_message = file_get_contents(dp_get_tmp_dir() . '/helpdesk-offline-message.txt');
		} else {
			try {
				$offline_message = App::getSetting('core.helpdesk_disabled_message');
			} catch (\Exception $e) {}
		}

		if (!$offline_message) {
			$offline_message = 'The helpdesk is currently offline for maintenance. Please try again soon.';
		}

		return $offline_message;
	}
}