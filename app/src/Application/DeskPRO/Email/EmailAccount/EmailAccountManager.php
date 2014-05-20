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
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\FetcherStorageFactory;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\TransportFactory;
use Application\DeskPRO\Email\EmailAccount\Repository\EmailAccountRepository;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\TicketGatewayProcessor;
use Application\DeskPRO\Entity\EmailAccount;
use Orb\Util\Arrays;

class EmailAccountManager
{
	const IS_ENABLED     = 1;
	const WITH_TRANSPORT = 2;
	const WITH_FETCHER   = 4;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\Repository\EmailAccountRepository
	 */
	private $repos;

	/**
	 * @var OutgoingAccount\TransportFactory
	 */
	private $transport_factory;

	/**
	 * @var IncomingAccount\FetcherStorageFactory
	 */
	private $fetcher_storage_factory;

	/**
	 * Array of string=>EmailAccount[]
	 * @var array
	 */
	private $email_address_map;

	/**
	 * Array of transports keyed by email account
	 * @var \Swift_Transport[]
	 */
	private $loaded_transports = array();

	/**
	 * Array of fetcher storages keyed by email account
	 * @var \Application\DeskPRO\EmailGateway\FetcherStorage\FetcherStorageInterface[]
	 */
	private $loaded_fetcher_storages = array();


	/**
	 * @param EmailAccountRepository $repos
	 * @param TransportFactory $transport_factory
	 * @param FetcherStorageFactory $fetcher_storage_factory
	 */
	public function __construct(EmailAccountRepository $repos, TransportFactory $transport_factory, FetcherStorageFactory $fetcher_storage_factory)
	{
		$this->repos                   = $repos;
		$this->transport_factory       = $transport_factory;
		$this->fetcher_storage_factory = $fetcher_storage_factory;
	}


	####################################################################################################################
	# Working with EmailAccounts
	####################################################################################################################

	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\EmailAccount|null
	 * @throws \OutOfBoundsException
	 */
	public function getAccount($id)
	{
		$acc = $this->repos->getAccount($id);
		if ($acc === null) {
			throw new \OutOfBoundsException();
		}
		return $acc;
	}


	/**
	 * @param $id
	 * @return \Application\DeskPRO\Entity\EmailAccount|null
	 * @throws \OutOfBoundsException
	 */
	public function getActiveAccount($id)
	{
		$acc = $this->getAccount($id);
		if ($acc->is_enabled) {
			return $acc;
		}

		throw new \OutOfBoundsException();
	}


	/**
	 * @param int|string $criteria Standard criteria filter
	 * @return \Application\DeskPRO\Entity\EmailAccount[]
	 */
	public function getAllAccounts($criteria = 0)
	{
		if ($criteria) {
			return $this->filterAccountCollection($this->repos->getAccounts(), $criteria);
		} else {
			return $this->repos->getAccounts();
		}
	}


	/**
	 * @param int|string $criteria Standard criteria filter
	 * @return \Application\DeskPRO\Entity\EmailAccount[]
	 */
	public function getAllActiveAccounts($criteria = 0)
	{
		if ($criteria) {
			return $this->filterAccountCollection($this->repos->getEnabledAccounts(), $criteria);
		} else {
			return $this->repos->getEnabledAccounts();
		}
	}


	/**
	 * @param int $id
	 * @return bool
	 */
	public function hasAcccount($id)
	{
		return $acc = $this->repos->getAccount($id) !== null;
	}


	/**
	 * @param string $id
	 * @return bool
	 */
	public function hasActiveAccount($id)
	{
		if (!$this->hasAcccount($id)) {
			return false;
		}

		$acc = $this->getAccount($id);
		return $acc->is_enabled;
	}


	/**
	 * Find an email account for a given email address
	 *
	 * @param string     $address    The address to search for
	 * @param int|string $criteria   Criteria. Use constants, or a string of the constant names like 'is_enabled|with_transport'
	 * @return EmailAccount|null
	 */
	public function findAccountForEmailAddress($address, $criteria = 0)
	{
		if ($this->email_address_map === null) {
			$this->email_address_map = $this->buildEmailAddressMap();
		}

		$address = strtolower($address);

		if (isset($this->email_address_map[$address])) {
			$self = $this;
			return Arrays::findValue($this->email_address_map[$address], function($account) use ($criteria, $self) {
				return $self->checkAccountCriteriaMatch($account, $criteria);
			});
		}

		return null;
	}


	/**
	 * @param array $accounts
	 * @param int $criteria
	 * @return array
	 */
	public function filterAccountCollection(array $accounts, $criteria = 0)
	{
		if (!$criteria) {
			return $accounts;
		}

		$self = $this;

		return array_filter($accounts, function($a) use ($self, $criteria) { return $self->checkAccountCriteriaMatch($a, $criteria); });
	}


	/**
	 * @param EmailAccount $account
	 * @param $criteria
	 * @return bool
	 */
	public function checkAccountCriteriaMatch(EmailAccount $account, $criteria)
	{
		if ($criteria && is_string($criteria)) {
			$parts = explode('|', $criteria);
			$criteria = 0;
			foreach ($parts as $p) {
				$p = trim($p);
				$p_name = 'Application\\DeskPRO\\Email\\EmailAccount\\EmailAccountManager::' . strtoupper($p);
				$p_val = constant($p_name);
				if ($p_val) {
					$criteria = $criteria | $p_val;
				}
			}
		}

		// Check for enabled
		if ($criteria && $criteria & self::IS_ENABLED && !$account->is_enabled) {
			return false;
		}

		// Check for transport
		if ($criteria && $criteria & self::WITH_TRANSPORT && !$this->accountHasTransport($account)) {
			return false;
		}

		// Check for fetcher
		if ($criteria && $criteria & self::WITH_FETCHER && !$this->accountHasFetcherStorage($account)) {
			return false;
		}

		return true;
	}


	/**
	 * @return array
	 */
	private function buildEmailAddressMap()
	{
		$map = array();

		foreach ($this->getAllAccounts() as $acc) {
			$addr = strtolower($acc->address);

			if (!isset($map[$addr])) {
				$map[$addr] = array();
			}

			$map[$addr][] = $acc;

			foreach ($acc->other_addresses as $address) {
				$addr = strtolower($address);
				if (!isset($map[$addr])) {
					$map[$addr] = array();
				}
				$map[$addr][] = $acc;
			}
		}

		return $map;
	}


	/**
	 * "primary" just means the first account that has an outgoing.
	 *
	 * @return EmailAccount
	 */
	public function getPrimaryEmailAccount()
	{
		foreach ($this->getAllActiveAccounts() as $acc) {
			if ($this->accountHasTransport($acc)) {
				return $acc;
			}
		}

		return null;
	}


	/**
	 * Count how many outgoing email accounts are defined
	 *
	 * @return int
	 */
	public function countOutgoingAccounts()
	{
		return count($this->getAllActiveAccounts('with_transport'));
	}


	####################################################################################################################
	# Working with Transports
	####################################################################################################################

	/**
	 * @return TransportFactory
	 */
	public function getTransportFactory()
	{
		return $this->transport_factory;
	}


	/**
	 * @param int|EmailAccount $acc
	 * @return bool
	 */
	public function accountHasTransport($acc)
	{
		$acc = $this->verifyAccountParam($acc);
		return $acc->outgoing_account !== null;
	}


	/**
	 * @param int|EmailAccount $acc
	 * @return \Swift_Transport
	 * @throws \OutOfBoundsException
	 */
	public function getTransportForAccount($acc)
	{
		$acc = $this->verifyAccountParam($acc);
		if (!$acc->outgoing_account) {
			throw new \OutOfBoundsException();
		}

		$key = spl_object_hash($acc);

		if (isset($this->loaded_transports[$key])) {
			return $this->loaded_transports[$key];
		}

		$tr = $this->transport_factory->createTransport($acc->outgoing_account);
		$this->loaded_transports[$key] = $tr;

		return $tr;
	}


	/**
	 * Stops/closes the transport.
	 *
	 * @param int|EmailAccount $acc
	 * @throws \OutOfBoundsException
	 */
	public function closeTransportForAccount($acc)
	{
		$acc = $this->verifyAccountParam($acc);
		if (!$acc->outgoing_account) {
			throw new \OutOfBoundsException();
		}

		$key = spl_object_hash($acc);

		if (!isset($this->loaded_transports[$key])) {
			return;
		}

		$tr = $this->loaded_transports[$key];
		unset($this->loaded_transports[$key]);

		$tr->stop();
	}


	/**
	 * Closes all loaded transports
	 *
	 * @param array $collect_exceptions Provide a variable to put exceptions into
	 */
	public function closeAllTransports(&$collect_exceptions = null)
	{
		$collect_exceptions = array();

		foreach ($this->loaded_transports as $tr) {
			try {
				$tr->stop();
			} catch (\Exception $e) {
				$collect_exceptions = array('exception' => $e, 'transport' => $tr);
			}
		}

		$this->loaded_transports = array();
	}


	####################################################################################################################
	# Working with Fetchers
	####################################################################################################################

	/**
	 * @return FetcherStorageFactory
	 */
	public function getFetcherStorageFactory()
	{
		return $this->fetcher_storage_factory;
	}


	/**
	 * @param int|EmailAccount $acc
	 * @return bool
	 */
	public function accountHasFetcherStorage($acc)
	{
		$acc = $this->verifyAccountParam($acc);
		return $acc->incoming_account !== null;
	}


	/**
	 * @param int|EmailAccount $acc
	 * @return \Application\DeskPRO\EmailGateway\FetcherStorage\FetcherStorageInterface
	 * @throws \OutOfBoundsException
	 */
	public function getFetcherStorageForAccount($acc)
	{
		$acc = $this->verifyAccountParam($acc);
		if (!$acc->incoming_account) {
			throw new \OutOfBoundsException();
		}

		$key = spl_object_hash($acc);

		if (isset($this->loaded_fetcher_storages[$key])) {
			return $this->loaded_fetcher_storages[$key];
		}

		$fethcer = $this->fetcher_storage_factory->createFetcherStorage($acc->incoming_account);
		$this->loaded_fetcher_storages[$key] = $fethcer;

		return $fethcer;
	}


	/**
	 * Stops/closes the fethcer.
	 *
	 * @param int|EmailAccount $acc
	 * @throws \OutOfBoundsException
	 */
	public function closeFetcherStorageForAccount($acc)
	{
		$acc = $this->verifyAccountParam($acc);
		if (!$acc->incoming_account) {
			throw new \OutOfBoundsException();
		}

		$key = spl_object_hash($acc);

		if (!isset($this->loaded_fetcher_storages[$key])) {
			return;
		}

		$fetcher = $this->loaded_fetcher_storages[$key];
		unset($this->loaded_fetcher_storages[$key]);

		$fetcher->closeStorage();
	}


	/**
	 * Closes all loaded fethcers
	 *
	 * @param array $collect_exceptions Provide a variable to put exceptions into
	 */
	public function closeAllFetcherStorages(&$collect_exceptions = null)
	{
		$collect_exceptions = array();

		foreach ($this->loaded_fetcher_storages as $fetcher) {
			try {
				$fetcher->closeStorage();
			} catch (\Exception $e) {
				$collect_exceptions = array('exception' => $e, 'fetcher_storage' => $fetcher);
			}
		}

		$this->loaded_fetcher_storages = array();
	}


	####################################################################################################################
	# Working with processors
	####################################################################################################################

	/**
	 * @param EmailAccount $account
	 * @param AbstractReader $reader
	 * @param array $options
	 * @return TicketGatewayProcessor|null
	 */
	public function getEmailProcessor(EmailAccount $account, AbstractReader $reader, array $options = array())
	{
		if ($account->account_type != 'tickets') {
			return null;
		}

		$proc = new TicketGatewayProcessor($account, $reader, $options);
		return $proc;
	}


	####################################################################################################################

	/**
	 * @param $acc
	 * @return EmailAccount|null
	 * @throws \InvalidArgumentException
	 */
	private function verifyAccountParam($acc)
	{
		if (!$acc || !($acc instanceof EmailAccount)) {
			$acc = $this->getAccount($acc);
		}

		if (!$acc || !($acc instanceof EmailAccount)) {
			throw new \InvalidArgumentException();
		}

		return $acc;
	}
}