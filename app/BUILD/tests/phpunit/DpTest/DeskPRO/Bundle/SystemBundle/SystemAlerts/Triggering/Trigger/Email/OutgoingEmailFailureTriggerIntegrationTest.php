<?php

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
        $this->em->flush();

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
        $this->em->flush();

        return $event;
    }
}
