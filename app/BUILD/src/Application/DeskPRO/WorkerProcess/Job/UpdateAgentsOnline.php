<?php



namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\UserChatAgentsOnlineEvent;

/**
 * Updates agents online through dispatching event for action alerts.
 */
class UpdateAgentsOnline extends AbstractJob
{
    const DEFAULT_INTERVAL = 300; // 5 minutes

    public function run()
    {
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('agent_chat')) {
            $dataService     = $this->getContainer()->get('data.agent');
            $agentIds        = $dataService->getAgentsOnlineStatus();
            $eventDispatcher->dispatch(UpdateOnlineEvent::EVENT_NAME, new UpdateOnlineEvent($agentIds));
        }

        $techService = $this->getContainer()->get('messenger.service.tech');
        $agentIds    = array_map(function ($a) {
            return $a->getId();
        }, $techService->getAgentsOnline());
        $eventDispatcher->dispatch(UserChatAgentsOnlineEvent::EVENT_NAME, new UserChatAgentsOnlineEvent($agentIds));
    }
}
