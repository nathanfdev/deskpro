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

namespace DpBehat\System\Alerts;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\ExceptionEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use Doctrine\ORM\EntityManager;
use DpBehat\KernelAwareTrait;

/**
 * Class EventsContext.
 */
class EventsContext extends BaseContext implements KernelAwareContext
{
    use KernelAwareTrait;

    /**
     * @Given I have no logged system alert events
     */
    public function thereAreNoEventsInTheDb()
    {
        $incidents = $this->sysEm()->getRepository(AbstractEvent::class)->findAll();
        foreach ($incidents as $incident) {
            $this->sysEm()->remove($incident);
        }
        $this->sysEm()->flush();
    }

    /**
     * @Then there should be :expected system alert event(s)
     */
    public function assertEventsCount($expected)
    {
        $expected !== 'no' or $expected = 0;
        $actual                         = count($this->sysEm()->getRepository(AbstractEvent::class)->findAll());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @Then there should be 1 :type system alert
     */
    public function assertOneAlertOfType($type)
    {
        $events = $this->sysEm()->getRepository(AbstractEvent::class)->findAll();
        $this->assertEquals(1, $c = count($events), "Expected 1 event, found {$c}");
        $typeToClass = [
            'php_error' => ErrorEvent::class,
            'exception' => ExceptionEvent::class,
        ];
        $this->assertTrue($events[0] instanceof $typeToClass[$type]);
    }

    /**
     * @return EntityManager
     */
    protected function sysEm()
    {
        return $this->get('doctrine.orm.system_entity_manager');
    }
}
