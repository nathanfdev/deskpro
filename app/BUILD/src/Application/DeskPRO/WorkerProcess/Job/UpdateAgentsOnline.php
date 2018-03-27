<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;

/**
 * Updates agents online through dispatching event for action alerts.
 */
class UpdateAgentsOnline extends AbstractJob
{
    const DEFAULT_INTERVAL = 300; // 5 minutes

    public function run()
    {
        if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('agent_chat')) {
            $data_service     = $this->getContainer()->get('data.agent');
            $agent_ids        = $data_service->getAgentsOnlineStatus();
            $event_dispatcher = $this->getContainer()->get('event_dispatcher');
            $event_dispatcher->dispatch(UpdateOnlineEvent::EVENT_NAME, new UpdateOnlineEvent($agent_ids));
        }
    }
}
