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
 * @category Entities
 */

namespace Application\ApiBundle\PermissionStrategy;

use Application\ApiBundle\ApiUser;

class UserTypePermission implements PermissionStrategyInterface
{
	const ADMIN = 'admin';
	const AGENT = 'agent';
	const USER  = 'user';

	/**
	 * @var string
	 */
	private $type;


	/**
	 * @param string $type
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}


	/**
	 * {@inheritDoc}
	 */
	public function userHasPermission(ApiUser $api_user, $context_info = null)
	{
		$person = $api_user->person;
		if (!$person) return false;

		switch ($this->type) {
			case self::ADMIN: if ($person->is_agent && $person->can_admin) return true; break;
			case self::AGENT: if ($person->is_agent) return true; break;
			case self::USER:  if (!$person->is_deleted || !$person->is_disabled) return true; break;
		}

		return false;
	}
}