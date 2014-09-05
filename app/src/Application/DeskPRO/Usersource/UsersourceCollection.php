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
 * @subpackage
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\Entity\Usersource;

/**
 * Used to filter results down to what you want
 * UsersourceManager returns instances of this offering you a flexible filtering API
 */
class UsersourceCollection extends \ArrayObject
{
	/**
	 * @return UsersourceCollection
	 */
	public function forInterface($interface)
	{
		switch ($interface) {
			case 'user':
				return $this->configuredForUsers();
			case 'agent':
			case 'admin':
			case 'reports':
			case 'billing':
				return $this->configuredForAgents();
		}

		throw new \InvalidArgumentException("Unknown interface '$interface'");
	}


	/**
	 * Limits to this ID only, still allowing other filters to fit your criteria
	 *
	 * @param int $id id
	 * @return UsersourceCollection
	 */
	public function mustHaveId($id)
	{
		$filtered = array_filter(
			(array)$this, function (Usersource $us) use ($id) {
				return $us->id == $id;
			}
		);

		return new static($filtered);
	}

	/**
	 * @return UsersourceCollection
	 */
	public function configuredForAgents()
	{
		// select only agent enabled usersources
		$filtered = array_filter(
			(array) $this, function (Usersource $us) {
				return $us->is_enabled_agent;
			}
		);

		// order them for the agents
		usort($filtered, function ($us1, $us2) {
			if ($us1->display_order_agent == $us2->display_order_agent) {
				return 0;
			}

			if ($us1->display_order_agent > $us2->display_order_agent) {
				return -1;
			}

			return 1;
		});

		return new static($filtered);
	}

	/**
	 * @return UsersourceCollection
	 */
	public function configuredForUsers()
	{
		// select only user enabled usersources
		$filtered = array_filter(
			(array) $this, function (Usersource $us) {
				return $us->is_enabled_user;
			}
		);

		// order them for the user
		usort($filtered, function ($us1, $us2) {
			if ($us1->display_order_user == $us2->display_order_user) {
				return 0;
			}

			if ($us1->display_order_user > $us2->display_order_user) {
				return -1;
			}

			return 1;
		});

		return new static($filtered);
	}

	/**
	 * @return UsersourceCollection
	 */
	public function withCapability($capability)
	{
		$filtered = array_filter(
			(array) $this, function (Usersource $us) use ($capability) {
				return $us->isCapable($capability);
			}
		);

		return new static($filtered);
	}

	/**
	 * @return UsersourceCollection
	 */
	public function ofType($type)
	{
		$type = strtolower($type);

		$filtered = array_filter(
			(array) $this, function (Usersource $us) use ($type) {
				return strtolower($us->source_type) == $type;
			}
		);

		return new static($filtered);
	}


	/**
	 * @return \Orb\Auth\Adapter\AdapterInterface|null
	 */
	public function getFirstOrNull()
	{
		$arr = (array) $this;
		return array_pop($arr);
	}
}
