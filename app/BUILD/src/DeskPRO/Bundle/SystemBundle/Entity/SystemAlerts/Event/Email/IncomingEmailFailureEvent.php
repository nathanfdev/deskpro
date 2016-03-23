<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractExceptionEvent;
use Doctrine\ORM\Mapping as ORM;
use Zend\Mail\Exception\RuntimeException;

/**
 * Class IncomingEmailFailureEvent.
 *
 * @ORM\Entity
 */
class IncomingEmailFailureEvent extends AbstractExceptionEvent
{
    use EmailAccountData;

    /**
     * @param EmailAccount     $account
     * @param RuntimeException $exception
     * @param \DateTime|null   $date_created
     */
    public function __construct(EmailAccount $account, RuntimeException $exception, \DateTime $date_created = null)
    {
        $this->email_account_id      = $account->getId();
        $this->email_account_address = $account->getAddress();
        parent::__construct($exception, $date_created);
    }
}
