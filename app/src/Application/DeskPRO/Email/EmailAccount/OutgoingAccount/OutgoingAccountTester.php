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
 */

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Orb\Util\Strings;

class OutgoingAccountTester
{
    /**
     * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    private $account_config;

    /**
     * @var
     */
    private $exception;

    /**
     * @var bool
     */
    private $is_success = false;

    /**
     * @var \Swift_Plugins_Loggers_ArrayLogger
     */
    private $swift_arraylogger;

    /**
     * @var \Swift_Message
     */
    private $swift_message;

    public function __construct(AccountConfigInterface $account_config)
    {
        $this->swift_arraylogger = new \Swift_Plugins_Loggers_ArrayLogger();
        $this->account_config    = $account_config;
    }

    /**
     * Runs the test
     *
     * @param  string $to_address
     * @param  string $from_address
     * @param  string $subject
     * @param  string $message
     * @return bool
     */
    public function test($to_address, $from_address, $subject, $message)
    {
        $this->swift_message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setBody($message)
            ->setFrom($from_address)
            ->setTo($to_address);

        try {
            if (defined('DP_EMAIL_TRANSPORT_FACTORY') && DP_EMAIL_TRANSPORT_FACTORY) {
                $tr = call_user_func(DP_EMAIL_TRANSPORT_FACTORY, 'test', $this->account_config, $this->account_config->getType(), $this->account_config);
                if ($tr) {
                    $this->sendWithTransport($tr);
                    if (defined('DPC_IS_CLOUD')) {
                        $this->swift_arraylogger->clear();
                        $this->swift_arraylogger->add("Mail was accepted to DeskPRO queue server");
                    }
                } else {
                    throw new \RuntimeException("Custom transport factory did not return a transport");
                }
            } else {
                if ($this->account_config instanceof SmtpConfig) {
                    $this->_testSmtp($this->account_config);
                } elseif ($this->account_config instanceof GmailConfig) {
                    $this->_testGmail($this->account_config);
                } elseif ($this->account_config instanceof PhpMailConfig) {
                    $this->_testMail($this->account_config);
                } else {
                    $this->is_success = false;
                    $this->swift_arraylogger->add("Unknown account type: ".get_class($this->account_config));
                }
            }
        } catch (\Exception $e) {
            $this->swift_arraylogger->add("[error] ({$e->getCode()}) Failed: {$e->getMessage()}");
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
     * @param \Swift_Transport $transport
     */
    private function sendWithTransport(\Swift_Transport $transport)
    {
        $mailer = \Swift_Mailer::newInstance($transport);

        $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($this->swift_arraylogger));

        $failed = null;
        if (!$mailer->send($this->swift_message, $failed)) {
            $this->is_success = false;
        } else {
            $this->is_success = true;
        }
    }

    /**
     * Test with SMTP
     */
    private function _testSmtp($account_config)
    {
        /** @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig $account_config */;

        $this->swift_arraylogger->add("Testing SmtpAccount");

        $this->swift_arraylogger->add("[options] host: {$account_config->host}");
        $this->swift_arraylogger->add("[options] port: {$account_config->port}");
        $this->swift_arraylogger->add("[options] secure: {$account_config->secure_mode}");
        $this->swift_arraylogger->add("[options] username: {$account_config->user}");
        $this->swift_arraylogger->add("[options] password: {$account_config->password}");

        $transport = \Swift_SmtpTransport::newInstance(
            $account_config->host ?: 'localhost',
            $account_config->port ?: 25,
            $account_config->secure_mode
        );
        if ($account_config->user) {
            $transport->setUsername($account_config->user);
            $transport->setPassword($account_config->password);
        }

        $this->sendWithTransport($transport);
    }

    /**
     * Test with gmail
     */
    private function _testGmail($account_config)
    {
        /** @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\GmailConfig $account_config */

        $this->swift_arraylogger->add("Testing GmailAccount");
        $smtp = new SmtpConfig();
        $data = array(
            'user'        => $account_config->user,
            'password'    => $account_config->password,
            'host'        => 'smtp.gmail.com',
            'port'        => 465,
            'secure_mode' => 'ssl',
        );
        foreach ($data as $k => $v) {
            $smtp->$k = $v;
        }
        $this->_testSmtp($smtp);
    }

    /**
     * Test with mail
     */
    public function _testMail($account_config)
    {
        $this->swift_arraylogger->add("Testing PhpMailAccount");
        $this->swift_arraylogger->add("(No detailed logging is available using the PHP mail() transport.)");
        $transport = \Swift_MailTransport::newInstance();
        $this->sendWithTransport($transport);
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return Strings::standardEol($this->swift_arraylogger->dump());
    }
}
