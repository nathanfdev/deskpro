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

namespace Application\DeskPRO\Usersource\Adapter;

use Orb\Auth\Identity;
use Orb\Util\Arrays;

class ActiveDirectory extends AbstractAdapter
{
	public function getFieldsFromIdentity(Identity $identity)
	{
		$info = $identity->getRawData();
		return array(
			'name'             => isset($info['name']) ? $info['name'] : '',
			'first_name'       => isset($info['first_name']) ? $info['first_name'] : '',
			'last_name'        => isset($info['last_name']) ? $info['last_name'] : '',
			'email'            => isset($info['email_address']) ? $info['email_address'] : '',
			'email_confirmed'  => true,
			'picture_data'     => isset($info['picture_data']) ? $info['picture_data'] : null,
			'phone'            => isset($info['phone']) ? $info['phone'] : null,
		);
	}


	/**
	 * @return \Orb\Auth\Adapter\ActiveDirectory
	 */
	protected function _createAuthAdapterObject()
	{
		return new \Orb\Auth\Adapter\ActiveDirectory($this->usersource->options);
	}



	/**
	 * Find a user identity just by an email address.
	 *
	 * @param $email_address
	 * @return \Orb\Auth\Identity|null
	 */
	public function findIdentityByInput($email_address)
	{
		$usersource = clone $this->usersource;
		$usersource->setOption('bindRequiresDn', true);
		$adapter = $usersource->getAdapter()->getAuthAdapter();

		$adapter->setFormData(array(
			'username' => $email_address,
			'password' => '',
		));
		$rec = $adapter->findRecordViaEmail($email_address);

		$raw_info = null;
		if ($rec) {
			$raw_info = $rec;

			if (isset($rec['dn'])) {
				$raw_info['dn'] = $rec['dn'];
			} elseif (isset($rec['distinguishedname'])) {
				$raw_info['dn'] = $rec['distinguishedname'];
			} else {
				return null;
			}

			$raw_info['domain'] = $usersource->getOption('accountDomainName');

			if (!empty($rec['userprincipalname'])) {
				$raw_info['identity'] = $rec['userprincipalname'][0];
			} elseif (!empty($rec['samaccountname'])) {
				$raw_info['identity'] = $rec['samaccountname'][0];
			} elseif (!empty($rec['uid'])) {
				$raw_info['identity'] = $rec['uid'][0];
			} else {
				$raw_info['identity'] = $raw_info['dn'];
			}

			if (!empty($rec['givenname'])) {
				$raw_info['first_name'] = $rec['givenname'][0];
			} elseif (!empty($rec['sn'])) {
				$raw_info['last_name'] = $rec['sn'][0];
			}

			if (!empty($rec['name'])) {
				$raw_info['name'] = $rec['name'][0];
			} elseif (!empty($rec['cn'])) {
				$raw_info['name'] = $rec['cn'][0];
			}

			if (!empty($rec['mail'])) {
				$raw_info['email_address'] = $rec['mail'][0];
			} elseif (!empty($rec['userprincipalname']) && \Orb\Validator\StringEmail::isValueValid($rec['userprincipalname'][0])) {
				$raw_info['email_address'] = $rec['userprincipalname'][0];
			}
		}

		if ($raw_info) {
			$identity = new Identity($raw_info['identity'], $raw_info);
			return $identity;
		}

		return null;
	}


	/**
	 * @return array
	 */
	public function getCapabilities()
	{
		return array(
			'form_login',
			'find_identity'
		);
	}
}