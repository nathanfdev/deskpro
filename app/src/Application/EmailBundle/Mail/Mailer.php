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
 * @subpackage EmailBundle
 */

namespace Application\EmailBundle\Mail;

use Application\EmailBundle\Mail\Message\MessageFactoryInterface;

class Mailer extends \Swift_Mailer
{
    /**
     * @var MessageFactoryInterface
     */
    private $message_factory;

    /**
     * @param \Swift_Transport        $transport
     * @param MessageFactoryInterface $message_factory
     */
    public function __construct(\Swift_Transport $transport, MessageFactoryInterface $message_factory)
    {
        $tmpdir = dp_get_tmp_dir() . '/swiftmailer-cache';
        if (!is_dir(dp_get_tmp_dir() . '/swiftmailer-cache')) {
            if (!@mkdir($tmpdir, 0777, true)) {
                $tmpdir = sys_get_temp_dir() . '/dp-swiftmailer-cache';
                if (!is_dir($tmpdir)) {
                    @mkdir($tmpdir, 0777, true);
                }
            }
        }

        if (!is_dir($tmpdir) || !is_writable($tmpdir)) {
            // Fall back on system tmp dir
            $tmpdir = sys_get_temp_dir();
        }

        \Swift_Preferences::getInstance()->setTempDir($tmpdir);
        $GLOBALS['DP_SWIFTMAIL_TMPDIR'] = $tmpdir;

        parent::__construct($transport);

        $this->message_factory = $message_factory;
    }


    /**
     * @param string $service
     * @return \Swift_Message
     */
    public function createMessage($service = 'message')
    {
        $message = $this->message_factory->createMessage($service);

        if (!$message) {
            $message = parent::createMessage($service);
        }

        return $message;
    }
}