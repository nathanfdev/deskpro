<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;

require_once 'IncomingEmailFailureTriggerIntegrationTest.php';

/**
 * Class OutgoingEmailFailureTriggerIntegrationTest.
 *
 * This simply extends IncomingEmailFailureTriggerIntegrationTest and overrides factory methods as their criteria
 * are the same and only input data are different (differnt event types)
 */
class OutgoingEmailFailureTriggerIntegrationTest extends IncomingEmailFailureTriggerIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->trigger = $this->get('dp_sys.alerts.outgoing_email_failure_trigger');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $when
     * @param int    $account_id
     *
     * @return OutgoingEmailFailureEvent
     */
    protected function dummyFailure($when = 'now', $account_id = 1)
    {
        $event = new OutgoingEmailFailureEvent(
            $account_id, 'test@dev.lo', new RawTransportException(), new \DateTime($when));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }

    /**
     * @param string $when
     * @param int    $account_id
     *
     * @return OutgoingEmailSuccessEvent
     */
    protected function dummySuccess($when = 'now', $account_id = 1)
    {
        $event = new OutgoingEmailSuccessEvent($account_id, 'test@dev.lo', new \DateTime($when));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }
}
