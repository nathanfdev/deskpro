<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\EmailGateway\Fetcher\ImapSocket;
use Application\DeskPRO\NewSettings\SettingsBag;
use DpSys\LowError\SystemErrorHandler;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Zend\Mail\Protocol\Imap;

class IncomingAccountTester
{
    /**
     * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    private $account_config;

    /**
     * @var \Orb\Log\Logger
     */
    private $logger;

    /**
     * @var \Orb\Log\Writer\ArrayWriter
     */
    private $logger_writer;

    /**
     * @var
     */
    private $exception;

    /**
     * @var bool
     */
    private $is_success = false;

    /**
     * @var array
     */
    private $message_count = 0;

    /**
     * @var SettingsBag
     */
    private $settings;

    public function __construct(AccountConfigInterface $account_config, SettingsBag $settings)
    {
        $this->account_config = $account_config;

        $this->logger        = new Logger();
        $this->logger_writer = new ArrayWriter();
        $this->logger->addWriter($this->logger_writer);
        $this->settings = $settings;
    }

    /**
     * Run the test.
     *
     * @return bool
     */
    public function test()
    {
        switch ($this->account_config->getType()) {
            case 'pop3':
                $this->_testPop3();
                break;

            case 'imap':
                $this->_testImap();
                break;

            case 'exchange':
                $this->_testExchange();
                break;

            case 'gmail':
                $this->_testGmail();
                break;

            case 'office365':
                $this->_testOffice365();
                break;
        }

        return $this->is_success;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->is_success;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * As part of the test, we fetch the count of messages.
     *
     * @return array|int
     */
    public function getMessageCount()
    {
        return $this->message_count;
    }

    /**
     * Tests Pop3.
     */
    private function _testPop3()
    {
        /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config $account_config */
        $account_config = $this->account_config;

        $this->logger->logInfo('Testing Pop3Account');

        try {
            $storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3([
                'host'                    => $account_config->host,
                'user'                    => $account_config->user,
                'password'                => $account_config->password,
                'port'                    => $account_config->port,
                'ssl'                     => $account_config->secure_mode,
                'disable_cert_validation' => $account_config->disable_cert_validation,
                'logger'                  => $this->logger,
                'test_mode'               => true,
            ]);

            $this->message_count = $storage->countMessages();

            $this->is_success = true;
        } catch (\Exception $e) {
            $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
            $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
            $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
            $this->is_success = false;
        }
    }

    private function _testImap()
    {
        /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig $account_config */
        $account_config = $this->account_config;

        $this->logger->logInfo('Testing ImapAccount');

        try {
            $storage = new \Application\DeskPRO\EmailGateway\Storage\Imap([
                'host'          => $account_config->host,
                'user'          => $account_config->user,
                'password'      => $account_config->password,
                'port'          => $account_config->port,
                'secure'        => $account_config->secure_mode,
                'no_validation' => $account_config->no_validation,
                'logger'        => $this->logger,
                'test_mode'     => true,
            ]);
            if ($account_config->read_mailbox) {
                $storage->ensureMailboxExists($account_config->read_mailbox);
                $storage->setMailBox($account_config->read_mailbox);
            }

            if ($account_config->mode == 'read') {
                $ids = $storage->getAllUnseenMessageUids();
            } else {
                $ids = $storage->getAllMessageUids();
            }

            $this->logger->logInfo('Read IDs: '.implode(', ', $ids));
            $this->message_count = count($ids);

            $this->is_success = true;
        } catch (\Exception $e) {
            $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
            $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
            $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
            $this->is_success = false;
        }
    }

    private function _testExchange()
    {
        /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig $account_config */
        $account_config = $this->account_config;

        $this->logger->logInfo('Testing ExchangeAccount');

        try {
            $storage = new \Application\DeskPRO\EmailGateway\Storage\Exchange([
                'host'       => $account_config->host,
                'user'       => $account_config->user,
                'password'   => $account_config->password,
                'port'       => $account_config->port,
                'logger'     => $this->logger,
                'test_mode'  => true,
                'is_verbose' => true,
            ]);
            if ($account_config->read_mailbox) {
                $storage->ensureFolderExists($account_config->read_mailbox);
            }

            $unread_only = false;
            $folder      = null;

            if ($account_config->mode == 'read') {
                $unread_only = true;
            }
            if ($account_config->read_mailbox) {
                $folder = $account_config->read_mailbox;
            }

            $ids = $storage->searchIds(100, $unread_only, $folder);
            $this->logger->logInfo('Read IDs: '.implode(', ', $ids));
            $this->message_count = count($ids);

            $this->is_success = true;
        } catch (\EWS_Exception $e) {
            switch ($e->getCode()) {
                case '401':
                    $this->logger->logError('Your username or password is incorrect.');
                    break;
                case '0':
                    if ($e->getMessage() == 'looks like we got no XML document') {
                        $this->logger->logError("It looks like the service URL is incorrect. Double-check the URL. It usually looks something like 'https://ews.example.com/EWS/Exchange.asmx'.");
                    }
                    break;
                case '404':
                    $this->logger->logError("The API endpoint returned a 404 Not Found. Double-check the URL. It usually looks something like 'https://ews.example.com/EWS/Exchange.asmx'.");
                    break;
                case '403':
                    $this->logger->logError('The API endpoint is returning a 403 Forbidden status code. This means that the user you provided does not have permission to use the service. '
                    ."This could mean that the user doesn't have permission to use the service from this network, or it could be that the specific services that DeskPRO requires are not allowed. "
                    .'You should ask your sysadmin to check the permissions on this user.');
                    break;
                default:
                    $this->logger->logError('Unknown error. Details:');
            }

            $this->logger->logError('Last request: '.$storage->getLastRequest());
            $this->logger->logError('Last response: '.$storage->getLastResponse());
            $this->logger->logError(str_repeat('-', 35));
            $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
            $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
            $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
            $this->is_success = false;
        } catch (\Exception $e) {
            switch ($e->getCode()) {
                case '0':
                    if ($e->getMessage() == 'looks like we got no XML document') {
                        $this->logger->logError("It looks like the service URL is incorrect. Double-check the URL. It usually looks something like 'https://ews.example.com/EWS/Exchange.asmx'.");
                    }
                    break;
                default:
                    $this->logger->logError('Unknown error. Details:');
            }

            $this->logger->logError(str_repeat('-', 35));
            $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
            $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
            $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
            $this->is_success = false;
        }
    }

    /**
     * Tests Gmail.
     */
    private function _testGmail()
    {
        /** @var GmailConfig $account_config */
        $account_config = $this->account_config;

        $this->logger->logInfo('Testing GmailAccount');

        if ($account_config->type === GmailConfig::TYPE_OAUTH) {
            $options = ImapSocket::initOptions($account_config, $this->settings);

            try {
                $protocol = new Imap($options['host'], $options['port'], 'ssl');
                ImapSocket::oauth2Authenticate($options['user'], $options['token'], $protocol);
                $storage             = new \Zend\Mail\Storage\Imap($protocol);
                $this->message_count = $storage->countMessages();
                $this->is_success    = true;
            } catch (\Exception $e) {
                $this->exception = $e;
                $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
                $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
                $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
                $this->is_success = false;
            }

            return;
        }

        try {
            $storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3([
                'host'      => 'pop.gmail.com',
                'user'      => $account_config->user,
                'password'  => $account_config->password,
                'port'      => 995,
                'ssl'       => 'ssl',
                'logger'    => $this->logger,
                'test_mode' => true,
            ]);

            $this->message_count = $storage->countMessages();

            $this->is_success = true;
        } catch (\Exception $e) {
            $this->exception = $e;
            $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
            $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
            $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
            $this->is_success = false;
        }
    }

    /**
     * Tests Office365.
     */
    private function _testOffice365()
    {
        /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Office365Config $config */
        $config = $this->account_config;

        $this->logger->logInfo('Testing Office365Account');

        try {
            $storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3([
                'host'      => 'outlook.office365.com',
                'user'      => $config->user,
                'password'  => $config->password,
                'port'      => 995,
                'ssl'       => 'ssl',
                'logger'    => $this->logger,
                'test_mode' => true,
            ]);

            $this->message_count = $storage->countMessages();

            $this->is_success = true;
        } catch (\Exception $e) {
            $this->exception = $e;
            $this->logger->logError(sprintf('Error: %s', $e->getMessage()));
            $this->logger->logError(sprintf('(Code: %s:%s)', get_class($e), $e->getCode()));
            $this->logger->logError(SystemErrorHandler::formatBacktrace($e->getTrace()));
            $this->is_success = false;
        }
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->logger_writer->getMessagesAsString();
    }
}
