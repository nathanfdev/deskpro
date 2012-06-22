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
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Util\Arrays;

use Orb\Log\Logger;
use Orb\Log\Loggable;

class ActiveDirectory implements FormLoginInterface, Loggable
{
	const OPT_HOST               = 'host';
	const OPT_PORT               = 'port';
	const OPT_TLS                = 'useStartTls';
	const OPT_SSL                = 'useSsl';
	const OPT_BASE_DN            = 'baseDn';
	const OPT_DOMAIN_NAME        = 'accountDomainName';
	const OPT_DOMAIN_NAME_SHORT  = 'accountDomainNameShort';
	const OPT_FILTER_FORMAT      = 'accountFilterFormat';
	const OPT_LOOKUP_USERNAME    = 'username';
	const OPT_LOOKUP_PASSWORD    = 'password';

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	/**
	 * @var string
	 */
	protected $set_username;
	/**
	 * @var string
	 */
	protected $set_password;

	protected $options = array(
		self::OPT_HOST               => 'localhost',
		self::OPT_PORT               => null, // null means default of 389 or 636 if ssl enabled
		self::OPT_TLS                => false,
		self::OPT_SSL                => false,
		self::OPT_BASE_DN            => '',
		self::OPT_DOMAIN_NAME        => '',
		self::OPT_DOMAIN_NAME_SHORT  => '',
		self::OPT_FILTER_FORMAT      => false,
		self::OPT_LOOKUP_USERNAME    => null,
		self::OPT_LOOKUP_PASSWORD    => null,
	);

	public function __construct(array $options)
	{
		$this->options = array_merge($this->options, $options);

		if (!empty($this->options[self::OPT_FILTER_FORMAT])) {
			$this->options['accountFilterFormat'] = '(&(objectClass=user)(' . $this->options['self::OPT_FILTER_FIELD'] . '=%s))';
		}

		$this->options['accountCanonicalForm'] = 3;
	}


	/**
	 * @param string $username
	 * @param string $password
	 */
	public function setFormData(array $form_data)
	{
		$this->set_username = !empty($form_data['username']) ? (string)$form_data['username'] : '';
		$this->set_password = !empty($form_data['password']) ? (string)$form_data['password'] : '';
	}


	/**
	 * @return \Zend\Authentication\Adapter\Ldap
	 */
	public function getZendAuthAdapter()
	{
		return new \Zend\Authentication\Adapter\Ldap($this->options, $this->set_username, $this->set_password);
	}


	/**
	 * Authenticate a user.
	 *
	 * @return
	 */
	public function authenticate()
	{
		if (!$this->set_username) {
			return new Result(Result::FAILURE, null, array('error_code' => 'missing_input_username', 'error_message' => 'No username provided'));
		}
		if (!$this->set_password) {
			return new Result(Result::FAILURE, null, array('error_code' => 'missing_input_password', 'error_message' => 'No password provided'));
		}

		$time_start = microtime(true);
		if ($this->logger) {
			$this->logger->log("START ActiveDirectory::authenticate", Logger::DEBUG);
			$this->logger->log("Options: " . trim(print_r($this->options,1)), Logger::DEBUG);
			$this->logger->log("Request: {$this->set_username}:{$this->set_password}", Logger::DEBUG);
		}

		$auth = $this->getZendAuthAdapter();

		try {
			/** @var $result \Zend\Authentication\Result */
			$result = $auth->authenticate();
		} catch (\Exception $e) {
			if ($this->logger) {
				$this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR);
			}
			return new Result(Result::FAILURE_EXCEPTION, null, array('error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e));
		}

		if ($this->logger) {
			foreach ($result->getMessages() as $msg) {
				$this->logger->log($msg, \Orb\Log\Logger::DEBUG);
			}

			$this->logger->log(sprintf("END ActiveDirectory::authenticate (took %.4fs)", microtime(true)-$time_start), Logger::DEBUG);
		}

		if (!$result->isValid()) {
			return new Result(Result::FAILURE_INVALID_CREDS, null, array('error_code' => 'invalid_credentials', 'error_message' => 'Invalid username or password'));
		}

		$raw_info = array();
		$raw_info['identity_friendly'] = $result->getIdentity();

		try {
			/** @var $ldap \Zend\Ldap\Ldap */
			$ldap = $auth->getLdap();
			$dn = $ldap->getCanonicalAccountName($result->getIdentity(), \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);

			/** @var $rec \Zend\Ldap\Node */
			$rec = $ldap->getNode($dn);
			if ($rec) {
				$raw_info = array_merge($raw_info, $rec->getAttributes());

				if ($rec->getAttribute('givenName')) {
					$raw_info['first_name'] = $rec->getAttribute('givenName');
				}
				if ($rec->getAttribute('SN')) {
					$raw_info['last_name'] = $rec->getAttribute('SN');
				}

				if ($rec->getAttribute('givenName') && $rec->getAttribute('SN')) {
					$raw_info['name'] = $rec->getAttribute('givenName') . ' ' . $rec->getAttribute('SN');
				} elseif ($rec->getAttribute('name')) {
					$raw_info['name'] = $rec->getAttribute('name');
				} elseif ($rec->getAttribute('CN')) {
					$raw_info['name'] = $rec->getAttribute('CN');
				}

				if ($rec->getAttribute('mail')) {
					$raw_info['email_address'] = $rec->getAttribute('mail');
				}
			}
		} catch (\Exception $e) {}

		$identity = new Identity($result->getIdentity(), $raw_info);

		return new Result(Result::SUCCESS, $identity);
	}


	/**
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(\Orb\Log\Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}
