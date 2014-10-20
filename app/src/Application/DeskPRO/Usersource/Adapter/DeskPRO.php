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
 * @subpackage Usersource
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\Adapter\Local;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

/**
 * The local DeskPRO login usersource
 *
 * @package Application\DeskPRO\Usersource\Adapter
 */
class DeskPRO extends AbstractAdapter implements IdentityFinderInterface
{
	public function getFieldsFromIdentity(Identity $identity)
	{
		return $identity->getRawData();
	}


	public function findIdentityByInput($input)
	{
		/** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
		$personRepo = App::getOrm()->getRepository('DeskPRO:Person');
		if ($person = $personRepo->findOneByEmail($input)) {
			return $person;
		}

		return null;
	}


	/**
	 * @return \Orb\Auth\Adapter\Google
	 */
	protected function _createAuthAdapterObject()
	{
		return new Local(App::getContainer()->getEm());
	}


	/**
	 * @return array
	 */
	public function getCapabilities()
	{
		return array(
			UsersourceInfo::CAPABILITY_FORM_LOGIN,
			UsersourceInfo::CAPABILITY_FIND_IDENTITY
		);
	}


	/**
	 * @param  mixed $capability
	 * @return bool
	 */
	public function isCapable($capability)
	{
		return in_array($capability, $this->getCapabilities());
	}
}
