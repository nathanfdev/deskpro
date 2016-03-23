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
 * Class AbstractTrigger.
 *
 * {@inheritdoc}
 */
abstract class AbstractTrigger implements Trigger
{
    /**
     * @var Incident[] Incident subject unique ID => Incident instance
     *
     * @see \DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event::getSubjectUniqueId()
     */
    protected $rising_incidents = [];

    /**
     * @var callable Executed when the issue is created
     */
    protected $raised_callback;

    /**
     * @var array Parameters to pass to the, will be prepended with an Incident instance
     */
    protected $raised_callback_params;

    /**
     * {@inheritdoc}
     */
    public function setRaisedCallback(callable $raised_callback, array $raised_callback_params = [])
    {
        $this->raised_callback        = $raised_callback;
        $this->raised_callback_params = $raised_callback_params;
    }

    /**
     * {@inheritdoc}
     */
    public function consume(Event $event)
    {
        if ($this->supports($event)) {
            $subject = $event->getSubjectUniqueId();
            if (!array_key_exists($subject, $this->rising_incidents)) {
                $class                            = $this->getIncidentClass();
                $this->rising_incidents[$subject] = new $class();
            }

            $incident = $this->rising_incidents[$subject];
            $incident->addEvent($event);
            if ($this->isIncidentState($incident)) {
                $incident->setRaised(true);
                if ($this->raised_callback) {
                    call_user_func($this->raised_callback, $incident);
                }
                unset($this->rising_incidents[$subject]);
            }

            return $incident;
        }
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
     * Decides if incident criteria are met.
     *
     * @param Incident $incident
     *
     * @return bool
     */
    abstract protected function isIncidentState(Incident $incident);
}
