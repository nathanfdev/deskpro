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

namespace Application\EmailBundle\Mail;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\EmailBundle\SourceMapper\SourceMapperInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RawMailer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var EmailAccountManager
     */
    private $email_accounts;

    /**
     * @param SourceMapperInterface $source_mapper
     * @param EmailAccountManager $email_accounts
     * @param LoggerInterface $logger
     */
    public function __construct(SourceMapperInterface $source_mapper, EmailAccountManager $email_accounts, LoggerInterface $logger = null)
    {
        $this->source_mapper = $source_mapper;
        $this->email_accounts = $email_accounts;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Send a source record.
     *
     * @param array $source
     */
    public function send(array $source)
    {

    }
}