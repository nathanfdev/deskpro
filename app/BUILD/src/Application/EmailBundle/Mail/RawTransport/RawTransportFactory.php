<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\EmailBundle\Mail\RawTransport;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount;
use Application\DeskPRO\NewSettings\SettingsBag;
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
     * @var SettingsBag
     */
    private $settings;

    /**
     * @param LoggerInterface $logger
     * @param SettingsBag     $settings
     */
    public function __construct(LoggerInterface $logger, SettingsBag $settings)
    {
        $this->logger   = $logger;
        $this->settings = $settings;
    }

    /**
     * @param AccountConfigInterface $config
     *
     * @throws \InvalidArgumentException
     *
     * @return RawTransportInterface
     */
    public function createTransport(AccountConfigInterface $config)
    {
        if (function_exists('deskpro_mail_transport_override')) {
            $tr = deskpro_mail_transport_override($config, $this);
            if ($tr) {
                return $tr;
            }
        }

        switch ($config->getType()) {
            case 'smtp':
                $tr = $this->createSmtpTransport($config);
                break;
            case 'gmail':
                $tr = $this->createGmailTransport($config);
                break;
            case 'office365':
                $tr = $this->createOffice365Transport($config);
                break;
            case 'php_mail':
                $tr = $this->createPhpMailTransport($config);
                break;
            case 'exchange':
                $tr = $this->createExchangeTransport($config);
                break;
            default:
                $this->logger->error('Unknown account type: %s', $config->getType());
                throw new \InvalidArgumentException("Unknown account type: {$config->getType()}");
        }

        return $tr;
    }

    /**
     * @param OutgoingAccount\SmtpConfig $config
     * @param bool                       $disable_connect_log
     *
     * @return RawSmtpTransport
     */
    public function createSmtpTransport(OutgoingAccount\SmtpConfig $config, $disable_connect_log = false)
    {
        $tr = \Swift_SmtpTransport::newInstance(
            $config->host ?: 'localhost',
            $config->port ?: 25,
            $config->secure_mode == 'none' ? null : $config->secure_mode
        );

        if ($config->secure_mode && $config->isDisableCertValidation()) {
            $tr->setStreamOptions(['ssl' => ['allow_self_signed' => true, 'verify_peer' => false]]);
        }

        if (!empty($config->user)) {
            $tr->setUsername($config->user);
        }
        if (!empty($config->password)) {
            $tr->setPassword($config->password);
        }

        $tr->setTimeout(120);

        $tr_logger = new TransportLogger($this->logger);
        if ($disable_connect_log) {
            $tr_logger->disableConnectionLog();
        }
        $tr->registerPlugin($tr_logger);

        return new RawSmtpTransport($tr);
    }

    /**
     * @param OutgoingAccount\GmailConfig $config
     *
     * @return RawSmtpTransport
     */
    public function createGmailTransport(OutgoingAccount\GmailConfig $config)
    {
        $tr = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl');
        $tr->setUsername($config->user);

        if ($config->type === OutgoingAccount\GmailConfig::TYPE_OAUTH) {
            $client = new \Google_Client();
            $client->setClientId($this->settings->get('core_email.google_oauth_client_id'));
            $client->setClientSecret($this->settings->get('core_email.google_oauth_service'));
            $client->setScopes([\Google_Service_Gmail::MAIL_GOOGLE_COM]);
            $client->setAccessToken($config->token);
            $client->setAccessType('offline');
            $client->refreshToken($config->refreshToken);
            $data = $client->getAccessToken();
            if (!empty($data['access_token'])) {
                $auth = new \Swift_Transport_Esmtp_Auth_XOAuth2Authenticator();
                $tr->setExtensionHandlers(['AUTH' => new \Swift_Transport_Esmtp_AuthHandler([$auth])]);
                $tr->setAuthMode('XOAUTH2');
                // setUsername again to assign username to AuthHandler
                $tr->setUsername($config->user);
                $tr->setPassword($data['access_token']);
            }
        } else {
            $tr->setPassword($config->password);
        }

        $tr->setTimeout(120);
        $tr->registerPlugin(new TransportLogger($this->logger));

        return new RawSmtpTransport($tr);
    }

    /**
     * @param OutgoingAccount\Office365Config $config
     *
     * @return RawSmtpTransport
     */
    public function createOffice365Transport(OutgoingAccount\Office365Config $config)
    {
        $tr = \Swift_SmtpTransport::newInstance('smtp.office365.com', 587, 'tls');
        $tr->setUsername($config->user);
        $tr->setPassword($config->password);
        $tr->setTimeout(120);
        $tr->registerPlugin(new TransportLogger($this->logger));

        return new RawSmtpTransport($tr);
    }

    /**
     * @param OutgoingAccount\PhpMailConfig $conifg
     *
     * @return RawSwiftmailerTransport
     */
    public function createPhpMailTransport(OutgoingAccount\PhpMailConfig $conifg)
    {
        $tr = \Swift_MailTransport::newInstance();

        $tr->registerPlugin(new TransportLogger($this->logger));

        return new RawSwiftmailerTransport($tr, new Rfc2822Decoder());
    }

    /**
     * @param OutgoingAccount\ExchangeConfig $config
     *
     * @return RawExchangeTransport
     */
    public function createExchangeTransport(OutgoingAccount\ExchangeConfig $config)
    {
        $decoder = new Rfc2822Decoder();

        return new RawExchangeTransport($config, $decoder, $this->logger);
    }
}
