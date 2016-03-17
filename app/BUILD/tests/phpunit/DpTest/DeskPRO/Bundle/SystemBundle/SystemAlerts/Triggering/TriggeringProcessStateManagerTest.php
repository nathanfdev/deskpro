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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Storage\KeyValueStorageInterface;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\StatefulIncidentTrigger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcessStateManager;
use Doctrine\ORM\EntityManager;
use DpTest\DeskProTestCase;

include_once '../_mocks.php';

/**
 * Class TriggeringProcessStateManagerTest.
 */
class TriggeringProcessStateManagerTest extends DeskProTestCase
{
    /**
     * @var Trigger[]
     */
    private $dummy_triggers = [];

    /**
     * @var KeyValueStorageInterface
     */
    private $key_value_storage;

    /**
     * @var Incident[]
     */
    private $incidents = [];

    protected function setUp()
    {
        $this->dummy_triggers = [
            new MockTrigger('First dummy state'),
            new MockTrigger2(['Second dummy array state']),
            new MockStatefulIncidentTrigger('Third dummy state', $this->incidents[1] = new MockIncident(1)),
            new MockStatefulIncidentTrigger2([42 => true], $this->incidents[42] = new MockIncident(42)),
        ];

        $this->key_value_storage = new MockKeyValueStorage();
    }

    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(TriggeringProcessStateManager::class, $this->instance());
    }

    /**
     * @test
     */
    public function it_should_save_triggers_internal_state_using_key_value_storage()
    {
        $this->assertNull($this->key_value_storage->get(TriggeringProcessStateManager::STORAGE_KEY));
        $this->instance()->saveState($this->dummy_triggers);
        $this->assertNotNull($serialised = $this->key_value_storage->get(TriggeringProcessStateManager::STORAGE_KEY));
    }

    /**
     * @test
     */
    public function it_should_restore_triggers_internal_state()
    {
        $instance = $this->instance();
        $instance->saveState($this->dummy_triggers);

        /** @var Trigger[] $triggers */
        $triggers = [
            new MockTrigger(),
            new MockTrigger2(),
            new MockStatefulIncidentTrigger(),
            new MockStatefulIncidentTrigger2(),
        ];
        $instance->provideState($triggers);

        $this->assertEquals('First dummy state', $triggers[0]->getState());
        $this->assertEquals(['Second dummy array state'], $triggers[1]->getState());
        $this->assertEquals('Third dummy state', $triggers[2]->getState());
        $this->assertEquals([42 => true], $triggers[3]->getState());
    }

    /**
     * @test
     */
    public function it_should_restore_triggers_continuing_incidents()
    {
        $instance = $this->instance();
        $instance->saveState($this->dummy_triggers);

        /** @var StatefulIncidentTrigger[] $triggers */
        $triggers = [
            new MockStatefulIncidentTrigger(),
            new MockStatefulIncidentTrigger2(),
        ];
        $instance->provideState($triggers);

        $this->assertSame($this->incidents[1], $triggers[0]->getContinuingIncident());
        $this->assertSame($this->incidents[42], $triggers[1]->getContinuingIncident());
    }

    /**
     * @test
     */
    public function it_should_work_if_new_trigger_was_added()
    {
        $instance = $this->instance();
        $instance->saveState($this->dummy_triggers);

        /** @var Trigger[] $triggers */
        $triggers = [new MockTrigger3('New trigger initial state')];
        $instance->provideState($triggers);

        $this->assertEquals('New trigger initial state', $triggers[0]->getState());
    }

    /**
     * @test
     */
    public function it_should_work_when_some_of_the_triggers_were_removed()
    {
        $instance = $this->instance();
        $instance->saveState($this->dummy_triggers);

        /** @var Trigger[] $triggers */
        $triggers = [new MockTrigger()];
        $instance->provideState($triggers);

        $this->assertEquals('First dummy state', $triggers[0]->getState());
    }

    /**
     * @return TriggeringProcessStateManager
     */
    private function instance()
    {
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->method('find')->willReturnCallback(function ($class, $id) {
            if ($class === Incident::class) {
                return $this->incidents[$id];
            }
        });

        return new TriggeringProcessStateManager($this->key_value_storage, $em);
    }
}
