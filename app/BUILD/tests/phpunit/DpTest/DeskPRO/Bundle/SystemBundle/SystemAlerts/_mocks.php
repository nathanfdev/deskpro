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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Storage\KeyValueStorageInterface;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\StatefulIncidentTrigger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

/**
 * Class MockEvent.
 */
class MockEvent extends Event
{
    public function __construct($processed = false)
    {
        parent::__construct();
        $this->setProcessed($processed);
    }
}

/**
 * Class MockEvent2.
 */
class MockEvent2 extends MockEvent
{
}

/**
 * Class DummyTrigger.
 */
trait DummyTrigger
{
    protected function supports(Event $event)
    {
        return $event instanceof MockEvent;
    }

    protected function initState()
    {
        $this->state = ['events_counter' => 0];
    }

    protected function isIncidentState()
    {
        return $this->state['events_counter'] >= 5;
    }

    protected function process(Event $event)
    {
        ++$this->state['events_counter'];
    }

    protected function createIncident()
    {
        return new MockIncident();
    }
}

/**
 * Class MockTrigger.
 */
class MockTrigger extends Trigger
{
    use DummyTrigger;

    public function __construct($state = null)
    {
        parent::__construct();
        if ($state) {
            $this->state = $state;
        }
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
class MockStatefulIncidentTrigger extends StatefulIncidentTrigger
{
    use DummyTrigger;

    public function __construct($state = null, $continuing_incident = null)
    {
        parent::__construct();
        if ($state) {
            $this->state = $state;
        }
        if ($continuing_incident) {
            $this->continuing_incident = $continuing_incident;
        }
    }

    protected function updateContinuingIncident()
    {
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
class MockIncident extends Incident
{
    public function __construct($id = null)
    {
        parent::__construct();
        if ($id) {
            $this->id = $id;
        }
    }

    public function getInstructions()
    {
        return "Relax, it's not a real incident";
    }
}

/**
 * Class MockKeyValueStorage.
 */
class MockKeyValueStorage implements KeyValueStorageInterface
{
    private $storage = [];

    public function save($key, $value)
    {
        $this->storage[$key] = $value;
    }

    public function get($key)
    {
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
    }

    public function remove($key)
    {
        if (array_key_exists($key, $this->storage)) {
            unset($this->storage[$key]);
        }
    }
}
