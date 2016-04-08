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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident;
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
}
