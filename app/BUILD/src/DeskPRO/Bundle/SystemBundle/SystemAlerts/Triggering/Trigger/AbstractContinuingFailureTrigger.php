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
namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractContinuingFailureIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\StatefulIncidentTrigger;
use Doctrine\ORM\EntityManager;

/**
 * Class AbstractContinuingFailureTrigger.
 *
 * Abstract trigger which rises incidents when failure events continue for a certain period.
 */
abstract class AbstractContinuingFailureTrigger extends StatefulIncidentTrigger
{
    /**
     * @var int Trigger will raise an incident only after consistent failures for minutes (the default 0
     *          value means immediate rising)
     */
    private $silence_time = 0;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param Event $event
     *
     * @return bool
     */
    abstract protected function isFailure(Event $event);

    /**
     * @param Event $event
     *
     * @return bool
     */
    abstract protected function isSuccess(Event $event);

    /**
     * @return Incident
     */
    abstract protected function instantiateIncident();

    /**
     * IncomingEmailFailureTrigger constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * @param int $silence_time
     */
    public function setSilenceTime($silence_time)
    {
        $this->silence_time = $silence_time;
    }

    /**
     * {@inheritdoc}
     */
    protected function initState()
    {
        $this->state = [
            'date_first_failure' => null,
            'date_last_failure'  => null,

            // IDs of the failure events are used to construct incident report
            'failing_event_ids' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function isIncidentState()
    {
        if ($this->state['date_first_failure'] && $this->state['date_last_failure']) {

            /** @var \DateInterval $diff */
            $diff = $this->state['date_last_failure']->diff($this->state['date_first_failure']);

            return $diff->i >= $this->silence_time;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function process(Event $event)
    {
        if ($this->isFailure($event)) {
            $this->state['date_first_failure'] or $this->state['date_first_failure'] = $event->getDateCreated();
            $this->state['date_last_failure']                                        = $event->getDateCreated();
            $this->state['failing_event_ids'][]                                      = $event->getId();
        }
        if ($this->isSuccess($event)) {
            $this->initState();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createIncident()
    {
        return $this->setIncidentInfo($this->instantiateIncident());
    }

    /**
     * {@inheritdoc}
     */
    protected function updateContinuingIncident()
    {
        return $this->setIncidentInfo($this->continuing_incident);
    }

    /**
     * @param AbstractContinuingFailureIncident $incident
     *
     * @return AbstractContinuingFailureIncident The passed incident instance with updated information
     */
    private function setIncidentInfo(AbstractContinuingFailureIncident $incident)
    {
        $incident->setDateFirstFailure($this->state['date_first_failure']);
        $incident->setDateLastFailure($this->state['date_last_failure']);
        $events = array_map(
            function ($id) {
                return $this->em->getReference(Event::class, $id);
            },
            $this->state['failing_event_ids']
        );
        $incident->setEvents($events);

        return $incident;
    }
}
