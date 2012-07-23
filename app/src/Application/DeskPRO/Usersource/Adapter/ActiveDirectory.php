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
		$adapter = $usersource->getAdapter();

		/** @var $zend_auth \Zend\Authentication\Adapter\Ldap */
		$zend_auth = $adapter->getAuthAdapter()->getZendAuthAdapter();

		// Bogus because zend only creates ldap obj when its needed,
		// so this is a hack to get it to set all the correct options
		// for us
		try {
			$zend_auth->setUsername('__bogus__');
			$zend_auth->setPassword('__bogus__');
			$zend_auth->authenticate();
		} catch (\Exception $e) {}

		/** @var $ldap \Zend\Ldap\Ldap */
		$ldap = $zend_auth->getLdap();

		$raw_info = null;

		$dn = $ldap->getCanonicalAccountName($email_address, \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);
		$rec = $ldap->getNode($dn);

		$raw_info = null;
		if ($rec) {
			$raw_info = array();

			if ($rec->getAttribute('userPrincipalName')) {
				$raw_info['identity'] = $rec->getAttribute('userPrincipalName', 0);
			} elseif ($rec->getAttribute('sAMAccountName')) {
				$raw_info['identity'] = $rec->getAttribute('sAMAccountName', 0);
			} elseif ($rec->getAttribute('uid')) {
				$raw_info['identity'] = $rec->getAttribute('uid', 0);
			} else {
				$raw_info['identity'] = $dn;
			}

			$raw_info['dn'] = $dn;

			if ($rec->getAttribute('givenName')) {
				$raw_info['first_name'] = $rec->getAttribute('givenName', 0);
			}
			if ($rec->getAttribute('sn')) {
				$raw_info['last_name'] = $rec->getAttribute('sn', 0);
			}

			if ($rec->getAttribute('name')) {
				$raw_info['name'] = $rec->getAttribute('name', 0);
			} elseif ($rec->getAttribute('cn')) {
				$raw_info['name'] = $rec->getAttribute('cn', 0);
			}

			if ($rec->getAttribute('mail')) {
				$raw_info['email_address'] = $rec->getAttribute('mail', 0);
			} elseif (\Orb\Validator\StringEmail::isValueValid($rec->getAttribute('userPrincipalName', 0))) {
				$raw_info['email_address'] = $rec->getAttribute('userPrincipalName', 0);
			}

			foreach ($raw_info as &$v) {
				if (is_array($v)) {
					$v = Arrays::getFirstItem($v);
				}
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