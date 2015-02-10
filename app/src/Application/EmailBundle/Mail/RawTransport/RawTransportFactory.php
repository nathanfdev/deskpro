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

namespace Application\EmailBundle\Mail\RawTransport;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount;
use Application\EmailBundle\Mail\RawMessage\Rfc2822Decoder;
use Application\EmailBundle\SwiftMailer\Plugins\TransportLogger;
use Psr\Log\LoggerInterface;

class RawTransportFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param  AccountConfigInterface    $config
     * @return RawTransportInterface
     * @throws \InvalidArgumentException
     */
    public function createTransport(AccountConfigInterface $config)
    {
        switch ($config->getType()) {
            case 'smtp':     $tr = $this->createSmtpTransport($config); break;
            case 'gmail':    $tr = $this->createGmailTransport($config); break;
            case 'office365':$tr = $this->createOffice365Transport($config); break;
            case 'php_mail': $tr = $this->createPhpMailTransport($config); break;
            case 'exchange': $tr = $this->createExchangeTransport($config); break;
            default:
                $this->logger->error("Unknown account type: %s", $config->getType());
                throw new \InvalidArgumentException("Unknown account type: {$config->getType()}");
        }

        return $tr;
    }


    /**
     * @param OutgoingAccount\SmtpConfig $config
     * @return RawSmtpTransport
     */
    public function createSmtpTransport(OutgoingAccount\SmtpConfig $config)
    {
        $tr = \Swift_SmtpTransport::newInstance(
            $config->host ?: 'localhost',
            $config->port ?: 25,
            $config->secure_mode == 'none' ? null : $config->secure_mode
        );

        if (!empty($config->user)) {
            $tr->setUsername($config->user);
        }
        if (!empty($config->password)) {
            $tr->setPassword($config->password);
        }

        $tr->setTimeout(120);
        $tr->registerPlugin(new TransportLogger($this->logger));

        $raw_tr = new RawSmtpTransport($tr);

        return $raw_tr;
    }


    /**
     * @param OutgoingAccount\GmailConfig $config
     * @return \Swift_SmtpTransport
     */
    public function createGmailTransport(OutgoingAccount\GmailConfig $config)
    {
        $tr = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl');
        $tr->setUsername($config->user);
        $tr->setPassword($config->password);
        $tr->setTimeout(120);
        $tr->registerPlugin(new TransportLogger($this->logger));

        $raw_tr = new RawSmtpTransport($tr);

        return $raw_tr;
    }

    /**
     * @param OutgoingAccount\Office365Config $config
     * @return \Swift_SmtpTransport
     */
    public function createOffice365Transport(OutgoingAccount\Office365Config $config)
    {
        $tr = \Swift_SmtpTransport::newInstance('smtp.office365.com', 587, 'tls');
        $tr->setUsername($config->user);
        $tr->setPassword($config->password);
        $tr->setTimeout(120);
        $tr->registerPlugin(new TransportLogger($this->logger));

        return $tr;
    }

    /**
     * @param OutgoingAccount\PhpMailConfig $conifg
     * @return \Swift_MailTransport
     */
    public function createPhpMailTransport(OutgoingAccount\PhpMailConfig $conifg)
    {
        $tr = \Swift_MailTransport::newInstance();

        $tr->registerPlugin(new TransportLogger($this->logger));

        $raw_tr = new RawSwiftmailerTransport($tr, new Rfc2822Decoder());

        return $raw_tr;
    }

    /**
     * @param OutgoingAccount\ExchangeConfig $config
     * @return RawExchangeTransport
     */
    public function createExchangeTransport(OutgoingAccount\ExchangeConfig $config)
    {
        $decoder = new Rfc2822Decoder();
        return new RawExchangeTransport($config, $decoder, $this->logger);
    }
}
