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

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use Application\DeskPRO\Entity\EmailAccount;
use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Zend\Mail\Exception\RuntimeException;

include_once 'BaseIntegrationTest.php';

/**
 * Class EventLoggerIntegrationTest.
 */
class EventLoggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var EmailAccount
     */
    private $email;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->email          = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $this->email->id      = 1;
        $this->email->address = 'test@dev.lo';
    }

    /**
     * @test
     */
    public function it_should_be_a_container_service()
    {
        $this->assertInstanceOf(EventLogger::class, $this->event_logger);
    }

    /**
     * @test
     */
    public function it_should_not_log_a_success_event_if_there_was_no_corresponding_failure()
    {
        $this->event_logger->log(new IncomingEmailSuccessEvent($this->email));
        $this->assertEquals(0, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_not_log_a_success_event_if_there_is_one_already()
    {
        $this->event_logger->log(new IncomingEmailFailureEvent($this->email, new RuntimeException()));
        $this->event_logger->log(new IncomingEmailSuccessEvent($this->email));
        $this->assertEquals(2, $this->countEvents());

        $this->event_logger->log(new IncomingEmailSuccessEvent($this->email));

        $this->assertEquals(2, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_log_a_single_success_record_from_series_when_there_was_a_corresponding_failure()
    {
        $this->event_logger->log(new IncomingEmailFailureEvent($this->email, new RuntimeException()));
        $this->assertEquals(1, $this->countEvents());

        for ($i = 0; $i < 5; ++$i) {
            $this->event_logger->log(new IncomingEmailSuccessEvent($this->email));
        }

        $this->assertEquals(2, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_log_a_success_event_despite_a_success_event_of_another_type()
    {
        $this->event_logger->log(new IncomingEmailFailureEvent($this->email, new RuntimeException()));
        $this->event_logger->log(new OutgoingEmailFailureEvent(
            $this->email->getId(), $this->email->getAddress(), new RawTransportException()));
        $this->event_logger->log(new OutgoingEmailSuccessEvent($this->email->getId(), $this->email->getAddress()));
        $this->assertEquals(3, $this->countEvents());

        $this->event_logger->log(new IncomingEmailSuccessEvent($this->email));

        $this->assertEquals(4, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_log_a_success_event_despite_a_success_event_of_the_same_type_but_with_another_subject()
    {
        $this->event_logger->log(new OutgoingEmailFailureEvent(1, '1@1.lo', new RawTransportException()));
        $this->event_logger->log(new OutgoingEmailFailureEvent(2, '2@2.lo', new RawTransportException()));
        $this->event_logger->log(new OutgoingEmailSuccessEvent(1, '1@1.lo'));
        $this->assertEquals(3, $this->countEvents());

        $this->event_logger->log(new OutgoingEmailSuccessEvent(2, '2@2.lo'));

        $this->assertEquals(4, $this->countEvents());
    }
}
