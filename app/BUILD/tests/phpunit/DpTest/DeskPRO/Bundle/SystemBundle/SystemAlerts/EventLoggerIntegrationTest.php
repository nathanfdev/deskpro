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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Zend\Mail\Exception\RuntimeException;

include_once 'BaseIntegrationTest.php';

/**
 * Class EventLoggerIntegrationTest.
 */
class EventLoggerIntegrationTest extends BaseIntegrationTest
{
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
        $this->event_logger->log(new IncomingEmailSuccessEvent());
        $this->assertEquals(0, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_not_log_a_success_event_if_there_is_one_already()
    {
        $this->event_logger->log(new IncomingEmailFailureEvent(new RuntimeException()));
        $this->event_logger->log(new IncomingEmailSuccessEvent());
        $this->assertEquals(2, $this->countEvents());

        $this->event_logger->log(new IncomingEmailSuccessEvent());

        $this->assertEquals(2, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_log_a_single_success_record_from_series_when_there_was_a_corresponding_failure()
    {
        $this->event_logger->log(new IncomingEmailFailureEvent(new RuntimeException()));
        $this->assertEquals(1, $this->countEvents());

        for ($i = 0; $i < 5; ++$i) {
            $this->event_logger->log(new IncomingEmailSuccessEvent());
        }

        $this->assertEquals(2, $this->countEvents());
    }
}
