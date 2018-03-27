<?php

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
