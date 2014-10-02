<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage
 */

namespace Orb\Auth\Adapter;

class DbTablePhpPasswordCheck extends DbTable
{
	const OPT_PASSWORD_PHP = 'password_php';

	protected function isValidPassword(array $userinfo, $password_input)
	{
		$field_id       = $this->options->get(self::OPT_FIELD_ID);
		$field_username = $this->options->get(self::OPT_FIELD_USERNAME);
		$field_email    = $this->options->get(self::OPT_FIELD_EMAIL);
		$field_password  = $this->options->get(self::OPT_FIELD_PASSWORD);

		$user_id       = $field_id && isset($userinfo[$field_id]) ? $userinfo[$field_id] : null;
		$user_username = $field_username && isset($userinfo[$field_username]) ? $userinfo[$field_username] : null;
		$user_email    = $field_email && isset($userinfo[$field_email]) ? $userinfo[$field_email] : null;

		$userinfo_password = $field_password && isset($userinfo[$field_password]) ? $userinfo[$field_password] : null;
		$password_recorded = $userinfo_password;

		if ($this->logger) $this->logger->logDebug("Found user record: $user_id $user_username $user_email");

		if (!$this->getDb()) {
			return false;
		}
		$db = $this->getDb();

		$is_valid = false;
		eval($this->options->get('password_php'));
		$is_valid = (bool)$is_valid;

		if ($is_valid) {
			if ($this->logger) $this->logger->logDebug("Passed password check");
		} else {
			if ($this->logger) $this->logger->logDebug("Failed password check");
		}

		return $is_valid;
	}
}