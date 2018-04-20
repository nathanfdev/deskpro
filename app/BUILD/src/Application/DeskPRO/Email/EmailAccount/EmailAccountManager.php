<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\FetcherStorageFactory;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\PhpMailConfig;
use Application\DeskPRO\Email\EmailAccount\Repository\EmailAccountRepository;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\TicketGatewayProcessor;
use Application\DeskPRO\Encryption\DpEnc;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Exception\MissingConfigurationException;
use Application\EmailBundle\Mail\RawTransport\RawTransportFactory;
use Application\EmailBundle\SwiftMailer\Message\MessageOptionsInterface;
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
     * @var RawTransportFactory
     */
    private $transport_factory;

    /**
     * @var IncomingAccount\FetcherStorageFactory
     */
    private $fetcher_storage_factory;

    /**
     * Array of string=>EmailAccount[].
     *
     * @var array
     */
    private $email_address_map;

    /**
     * Array of transports keyed by email account.
     *
     * @var \Swift_Transport[]
     */
    private $loaded_transports = [];

    /**
     * Array of fetcher storages keyed by email account.
     *
     * @var \Application\DeskPRO\EmailGateway\FetcherStorage\FetcherStorageInterface[]
     */
    private $loaded_fetcher_storages = [];

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    private $default_out_accounts = null;

    /**
     * @var DpEnc
     */
    private $enc;

    /**
     * @param EmailAccountRepository $repos
     * @param RawTransportFactory    $transport_factory
     * @param FetcherStorageFactory  $fetcher_storage_factory
     */
    public function __construct(EmailAccountRepository $repos, RawTransportFactory $transport_factory, FetcherStorageFactory $fetcher_storage_factory, DpEnc $enc)
    {
        $this->repos                   = $repos;
        $this->transport_factory       = $transport_factory;
        $this->fetcher_storage_factory = $fetcher_storage_factory;
        $this->enc                     = $enc;
    }

    //###################################################################################################################
    // Working with EmailAccounts
    //###################################################################################################################

    /**
     * @param int $id
     *
     * @throws \OutOfBoundsException
     *
     * @return \Application\DeskPRO\Entity\EmailAccount|null
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
     *
     * @throws \OutOfBoundsException
     *
     * @return \Application\DeskPRO\Entity\EmailAccount|null
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
     *
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
     *
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
     *
     * @return bool
     */
    public function hasAcccount($id)
    {
        return $this->repos->getAccount($id) !== null;
    }

    /**
     * @param string $id
     *
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
     * Find an email account for a given email address.
     *
     * @param string     $address  The address to search for
     * @param int|string $criteria Criteria. Use constants, or a string of the constant names like 'is_enabled|with_transport'
     *
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

            return Arrays::findValue($this->email_address_map[$address], function ($account) use ($criteria, $self) {
                return $self->checkAccountCriteriaMatch($account, $criteria);
            });
        }

        return;
    }

    /**
     * @param \Swift_Mime_Message $message
     *
     * @return \Application\DeskPRO\Entity\EmailAccount
     */
    public function findAccountForSwiftmailerMessage(\Swift_Mime_Message $message, $brand = null)
    {
        if ($message instanceof MessageOptionsInterface) {
            if ($message->getMessageOptions()->has(MessageOptionsInterface::OPT_ACCOUNT_ID)) {
                try {
                    $acc = $this->getAccount($message->getMessageOptions()->get(MessageOptionsInterface::OPT_ACCOUNT_ID));
                } catch (\Exception $e) {
                }

                if ($acc && $acc->is_enabled && $acc->outgoing_account) {
                    return $acc;
                }
            }
        }

        $from = $message->getFrom();
        foreach ($from as $email => $name) {
            $acc = $this->findAccountForEmailAddress($email, self::IS_ENABLED & self::WITH_TRANSPORT);
            if ($acc) {
                return $acc;
            }
        }

        return $this->getDefaultOutAccountWithFallback($brand);
    }

    /**
     * @param array $accounts
     * @param int   $criteria
     *
     * @return array
     */
    public function filterAccountCollection(array $accounts, $criteria = 0)
    {
        if (!$criteria) {
            return $accounts;
        }

        $self = $this;

        return array_filter($accounts, function ($a) use ($self, $criteria) {
            return $self->checkAccountCriteriaMatch($a, $criteria);
        });
    }

    /**
     * @param EmailAccount $account
     * @param $criteria
     *
     * @return bool
     */
    public function checkAccountCriteriaMatch(EmailAccount $account, $criteria)
    {
        if ($criteria && is_string($criteria)) {
            $parts    = explode('|', $criteria);
            $criteria = 0;
            foreach ($parts as $p) {
                $p      = trim($p);
                $p_name = 'Application\\DeskPRO\\Email\\EmailAccount\\EmailAccountManager::'.strtoupper($p);
                $p_val  = constant($p_name);
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
        $map = [];

        foreach ($this->getAllAccounts() as $acc) {
            $addr = strtolower($acc->address);

            if (!isset($map[$addr])) {
                $map[$addr] = [];
            }

            $map[$addr][] = $acc;

            foreach ($acc->other_addresses as $address) {
                $addr = strtolower($address);
                if (!isset($map[$addr])) {
                    $map[$addr] = [];
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
    public function getPrimaryTicketAccount()
    {
        foreach ($this->getAllActiveAccounts() as $acc) {
            if ($this->accountHasFetcherStorage($acc) && $this->accountHasTransport($acc)) {
                return $acc;
            }
        }

        throw new MissingConfigurationException();
    }

    /**
     * Just like getPrimaryTicketAccount except will fallback on a non-ticket account.
     *
     * @param Brand|int $brand
     *
     * @return EmailAccount
     */
    public function getPrimaryTicketAccountWithFallback($brand = null)
    {
        try {
            return $this->getPrimaryTicketAccount();
        } catch (MissingConfigurationException $e) {
            return $this->getDefaultOutAccountWithFallback($brand);
        }
    }

    /**
     * @param Ticket $ticket
     *
     * @return EmailAccount
     */
    public function getAccountForTicket(Ticket $ticket)
    {
        if ($ticket->email_account && $ticket->email_account->is_enabled && $ticket->email_account->outgoing_account) {
            return $ticket->email_account;
        }

        return $this->getPrimaryTicketAccountWithFallback($ticket->getBrand());
    }

    /**
     * Count how many outgoing email accounts are defined.
     *
     * @return int
     */
    public function countOutgoingAccounts()
    {
        return count($this->getAllActiveAccounts('with_transport'));
    }

    //###################################################################################################################
    // Working with Transports
    //###################################################################################################################

    /**
     * @param EmailAccount[] $defaultAccounts
     */
    public function setDefaultOutAccounts(array $defaultAccounts)
    {
        foreach ($defaultAccounts as $default) {
            if (!$default->outgoing_account || !$default->is_enabled) {
                throw new \InvalidArgumentException();
            }
        }

        $this->default_out_accounts = $defaultAccounts;
    }

    /**
     * @param Brand|int $brand
     *
     * @return EmailAccount
     */
    public function getDefaultOutAccount($brand = null)
    {
        if ($this->default_out_accounts) {
            if ($brand instanceof Brand) {
                $brand = $brand->getId();
            }
            $brand = (int) $brand; // in case of null would be converted to 0

            $account = null;

            if ($brand && isset($this->default_out_accounts[$brand])) { // we have brand id and account for it
                $account = $this->default_out_accounts[$brand];
            } elseif (isset($this->default_out_accounts['default'])) { // we have no brand but we have default one
                $account = $this->default_out_accounts['default'];
            }

            if ($account) { // return here only if we have something
                return $account;
            }
        }

        // try to find first account that has brand and matches our needs
        if ($brand) {
            foreach ($this->getAllActiveAccounts() as $acc) {
                if ($this->accountHasTransport($acc) && $acc->hasBrand($brand)) {
                    return $acc;
                }
            }
        }

        // otherwise try to find first account that matches our needs
        foreach ($this->getAllActiveAccounts() as $acc) {
            if ($this->accountHasTransport($acc)) {
                return $acc;
            }
        }

        throw new MissingConfigurationException();
    }

    /**
     * Just like getDefaultOutAccount except will create an anonymous mail() mailer when none exists.
     *
     * @param Brand|int $brand
     *
     * @return EmailAccount
     */
    public function getDefaultOutAccountWithFallback($brand = null)
    {
        try {
            return $this->getDefaultOutAccount($brand);
        } catch (MissingConfigurationException $e) {
            $acc = new EmailAccount('outgoing');

            if (!empty($_SERVER['HOST_NAME'])) {
                $acc->address = 'deskpro@'.$_SERVER['HOST_NAME'];
            } elseif (@php_uname('n')) {
                $acc->address = 'deskpro@'.php_uname('n');
            } else {
                $acc->address = 'deskpro@localhost';
            }

            $acc->is_enabled       = true;
            $acc->outgoing_account = new PhpMailConfig();

            return $acc;
        }
    }

    /**
     * @return RawTransportFactory
     */
    public function getTransportFactory()
    {
        return $this->transport_factory;
    }

    /**
     * @param int|EmailAccount $acc
     *
     * @return bool
     */
    public function accountHasTransport($acc)
    {
        $acc = $this->verifyAccountParam($acc);

        return $acc->outgoing_account !== null;
    }

    /**
     * @param int|EmailAccount $acc
     *
     * @throws \OutOfBoundsException
     *
     * @return \Swift_Transport
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

        $tr                            = $this->transport_factory->createTransport(EmailAccountUtil::decryptOutgoingAccount($acc->outgoing_account, $this->enc));
        $this->loaded_transports[$key] = $tr;

        return $tr;
    }

    /**
     * Stops/closes the transport.
     *
     * @param int|EmailAccount $acc
     *
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
     * Closes all loaded transports.
     *
     * @param array $collect_exceptions Provide a variable to put exceptions into
     */
    public function closeAllTransports(&$collect_exceptions = null)
    {
        $collect_exceptions = [];

        foreach ($this->loaded_transports as $tr) {
            try {
                $tr->stop();
            } catch (\Exception $e) {
                $collect_exceptions = ['exception' => $e, 'transport' => $tr];
            }
        }

        $this->loaded_transports = [];
    }

    //###################################################################################################################
    // Working with Fetchers
    //###################################################################################################################

    /**
     * @return FetcherStorageFactory
     */
    public function getFetcherStorageFactory()
    {
        return $this->fetcher_storage_factory;
    }

    /**
     * @param int|EmailAccount $acc
     *
     * @return bool
     */
    public function accountHasFetcherStorage($acc)
    {
        $acc = $this->verifyAccountParam($acc);

        return $acc->incoming_account !== null;
    }

    /**
     * @param int|EmailAccount $acc
     *
     * @throws \OutOfBoundsException
     *
     * @return \Application\DeskPRO\EmailGateway\FetcherStorage\FetcherStorageInterface
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

        $fethcer                             = $this->fetcher_storage_factory->createFetcherStorage(EmailAccountUtil::decryptIncomingAccount($acc->incoming_account, $this->enc));
        $this->loaded_fetcher_storages[$key] = $fethcer;

        return $fethcer;
    }

    /**
     * Stops/closes the fethcer.
     *
     * @param int|EmailAccount $acc
     *
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
     * Closes all loaded fethcers.
     *
     * @param array $collect_exceptions Provide a variable to put exceptions into
     */
    public function closeAllFetcherStorages(&$collect_exceptions = null)
    {
        $collect_exceptions = [];

        foreach ($this->loaded_fetcher_storages as $fetcher) {
            try {
                $fetcher->closeStorage();
            } catch (\Exception $e) {
                $collect_exceptions = ['exception' => $e, 'fetcher_storage' => $fetcher];
            }
        }

        $this->loaded_fetcher_storages = [];
    }

    //###################################################################################################################
    // Working with processors
    //###################################################################################################################

    /**
     * @param EmailAccount   $account
     * @param AbstractReader $reader
     * @param array          $options
     *
     * @return TicketGatewayProcessor|null
     */
    public function getEmailProcessor(EmailAccount $account, AbstractReader $reader, array $options = [])
    {
        if ($account->account_type != 'tickets') {
            return;
        }

        $proc = new TicketGatewayProcessor($account, $reader, $options);

        return $proc;
    }

    //###################################################################################################################

    /**
     * @param $acc
     *
     * @throws \InvalidArgumentException
     *
     * @return EmailAccount|null
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
