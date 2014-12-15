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

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;

class TransportFactory
{
    /**
     * @param  AccountConfigInterface    $config
     * @return \Swift_Transport
     * @throws \InvalidArgumentException
     */
    public function createTransport(AccountConfigInterface $config)
    {
        if (defined('DP_EMAIL_TRANSPORT_FACTORY') && DP_EMAIL_TRANSPORT_FACTORY) {
            $tr = call_user_func(DP_EMAIL_TRANSPORT_FACTORY, 'default', $config, $config->getType(), $config);
            if ($tr) {
                return $tr;
            }
        }

        switch ($config->getType()) {
            case 'smtp':     $tr = $this->createSmtpTransport($config); break;
            case 'gmail':    $tr = $this->createGmailTransport($config); break;
            case 'php_mail': $tr = $this->createPhpMailTransport($config); break;
            case 'sendmail': $tr = $this->createSendmailTransport($config); break;
            default:
                throw new \InvalidArgumentException("Unknown account type: {$config->getType()}");
        }

        $tr->__dp_logger = new \Application\EmailBundle\Mail\Plugins\Logger();
        $tr->registerPlugin($tr->__dp_logger);

        return $tr;
    }


    /**
     * @param  SmtpConfig           $config
     * @return \Swift_SmtpTransport
     */
    public function createSmtpTransport(SmtpConfig $config)
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

        return $tr;
    }


    /**
     * @param  GmailConfig          $config
     * @return \Swift_SmtpTransport
     */
    public function createGmailTransport(GmailConfig $config)
    {
        $tr = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl');
        $tr->setUsername($config->user);
        $tr->setPassword($config->password);
        $tr->setTimeout(120);

        return $tr;
    }

    /**
     * @param  PhpMailConfig        $conifg
     * @return \Swift_MailTransport
     */
    public function createPhpMailTransport(PhpMailConfig $conifg)
    {
        $tr = \Swift_MailTransport::newInstance();

        return $tr;
    }

    /**
     * @param  SendmailConfig           $config
     * @return \Swift_SendmailTransport
     */
    public function createSendmailTransport(SendmailConfig $config)
    {
        $tr = \Swift_SendmailTransport::newInstance("{$config->sendmail_path} -bs");

        return $tr;
    }
}
