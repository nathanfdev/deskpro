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

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractStatefulIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\AbstractStatefulIncidentTrigger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\AbstractTrigger;

/**
 * Class MockEvent.
 */
class MockEvent extends AbstractEvent
{
    public function __construct($processed = false, $id = null)
    {
        parent::__construct();
        $this->setProcessed($processed);
        $this->id = $id ?: uniqid();
    }

    public function getSubjectDescription()
    {
        return 'Mock event subject';
    }

    public function generateSubjectUniqueId()
    {
        return 'mock_event_subject_'.$this->id;
    }
}

/**
 * Class MockSuccessEvent.
 */
class MockSuccessEvent extends MockEvent implements SuccessEvent
{
    public function getFailureType()
    {
        return MockEvent::class;
    }
}

/**
 * Class MockEvent2.
 */
class MockEvent2 extends MockEvent
{
}

/**
 * Class MockTrigger.
 */
class MockTrigger extends AbstractTrigger
{
    protected function supports(Event $event)
    {
        return $event instanceof MockEvent;
    }

    protected function isIncidentState(Incident $incident)
    {
        return count($incident->getEvents()) >= 5;
    }

    public function getIncidentClass()
    {
        return MockIncident::class;
    }
}

/**
 * Class MockTrigger2.
 */
class MockTrigger2 extends MockTrigger
{
}

/**
 * Class MockTrigger3.
 */
class MockTrigger3 extends MockTrigger
{
}

/**
 * Class MockStatefulIncidentTrigger.
 */
class MockStatefulIncidentTrigger extends AbstractStatefulIncidentTrigger
{
    protected function supports(Event $event)
    {
        return $event instanceof MockEvent;
    }

    protected function isIncidentState(Incident $incident)
    {
        $events = $incident->getEvents();
        $count  = count($events);

        return $count >= 5 && !$events[$count - 1] instanceof SuccessEvent;
    }

    public function getIncidentClass()
    {
        return MockIncident::class;
    }
}

/**
 * Class MockStatefulIncidentTrigger2.
 */
class MockStatefulIncidentTrigger2 extends MockStatefulIncidentTrigger
{
}

/**
 * Class MockIncident.
 */
class MockIncident extends AbstractStatefulIncident
{
    public function __construct($id = null)
    {
        parent::__construct();
        if ($id) {
            $this->id = $id;
        }
    }

    public function getTitle()
    {
        return "Relax, it's not a real incident";
    }
}
