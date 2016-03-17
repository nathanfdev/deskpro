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
namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;

/**
 * Class Trigger.
 *
 * Base Trigger class. Trigger is an object consuming logged events of certain types and rising incidents.
 */
abstract class Trigger
{
    /**
     * @var mixed Often trigger will contain some internal state sequentially updated during logged events
     *            consumption, this state has no formal restrictions but it needs to be an efficient and easily serializable data
     *            structure sufficient to decide whether an incident should take place and sufficient to construct the incident.
     */
    protected $state;

    /**
     * @var callable Executed when the issue is created
     */
    private $raised_callback;

    /**
     * Trigger constructor.
     */
    public function __construct()
    {
        $this->initState();
    }

    /**
     * Consumes a logged event and returns an Incident if it's created or updated.
     *
     * @param Event $event
     *
     * @return Incident|null
     */
    public function consume(Event $event)
    {
        if ($this->supports($event)) {
            $this->process($event);
            if ($this->isIncidentState()) {
                $incident = $this->createIncident();
                if ($this->raised_callback) {
                    call_user_func($this->raised_callback, $incident);
                }
                $this->initState();

                return $incident;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param callable $raised_callback
     */
    public function setRaisedCallback(callable $raised_callback)
    {
        $this->raised_callback = $raised_callback;
    }

    /**
     * Whether trigger supports an Event.
     *
     * @param Event $event
     *
     * @return bool
     */
    abstract protected function supports(Event $event);

    /**
     * Initializes the internal state.
     *
     * This method is called on trigger instantiation and after incident creation. It should init/flush trigger internal
     * state maintained by the process() method
     */
    abstract protected function initState();

    /**
     * Decides if incident criteria are met.
     *
     * This method should analyse trigger's internal state maintained by the process() method to decide if an Incident
     * takes place
     *
     * @return bool
     */
    abstract protected function isIncidentState();

    /**
     * Event processing logic.
     *
     * This method receives all supported by trigger events from log and should handle trigger internal state so that
     * isIncidentState() has needed information to decide if an Incident should be raised
     *
     * @param Event $event
     */
    abstract protected function process(Event $event);

    /**
     * Instantiates an incident.
     *
     * (!) Note that this method should not persist an incident with entity manager, it should just create and return
     *
     * @return Incident
     */
    abstract protected function createIncident();
}
