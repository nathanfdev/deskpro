<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcess;
use Doctrine\ORM\EntityManager;
use DpTest\ApiTestCase;

/**
 * Class BaseIntegrationTest.
 */
abstract class BaseIntegrationTest extends ApiTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityManager
     */
    protected $default_em;

    /**
     * @var EventLogger
     */
    protected $event_logger;

    /**
     * @var TriggeringProcess
     */
    protected $triggering_process;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->em                 = $this->get('doctrine.orm.system_entity_manager');
        $this->default_em         = $this->get('doctrine.orm.default_entity_manager');
        $this->event_logger       = $this->get('dp_sys.alerts.event_logger');
        $this->triggering_process = $this->get('dp_sys.alerts.triggering_process');

        $events = $this->em->getRepository(AbstractEvent::class)->findAll();
        foreach ($events as $event) {
            $this->em->remove($event);
        }
        $incidents = $this->em->getRepository(AbstractIncident::class)->findAll();
        foreach ($incidents as $incident) {
            $this->em->remove($incident);
        }
        $this->em->flush();
    }

    /**
     * @return int
     */
    protected function countEvents()
    {
        return $this->em->createQuery('SELECT COUNT(e) FROM '.AbstractEvent::class.' e')->getSingleScalarResult();
    }

    /**
     * @return int
     */
    protected function countRaisedIncidents()
    {
        return $this->em->createQuery(
            'SELECT COUNT(i) FROM '.AbstractIncident::class.' i WHERE i.raised = true')->getSingleScalarResult();
    }

    /**
     * @return int
     */
    protected function countAllIncidents()
    {
        return $this->em->createQuery('SELECT COUNT(i) FROM '.AbstractIncident::class.' i')->getSingleScalarResult();
    }

    /**
     * @param string $type
     *
     * @return int
     */
    protected function countIncidents($type)
    {
        return $this->em->createQuery('SELECT COUNT(i) FROM '.$type.' i')->getSingleScalarResult();
    }

    /**
     * @return Incident|StatefulIncident
     */
    protected function findSingleIncident($turnedOn = true)
    {
        $incidents = $this->em->getRepository(AbstractIncident::class)->findAll();
        $this->assertCount($turnedOn ? 1 : 0, $incidents);

        return isset($incidents[0]) ? $incidents[0] : null;
    }
}
