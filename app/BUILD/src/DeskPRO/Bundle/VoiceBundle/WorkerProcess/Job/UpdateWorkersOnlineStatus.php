<?php

namespace DeskPRO\Bundle\VoiceBundle\WorkerProcess\Job;

use Application\DeskPRO\WorkerProcess\Job\AbstractJob;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

class UpdateWorkersOnlineStatus extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function run()
    {
        $serializer     = $this->getContainer()->get('serializer');
        $dispatcher     = $this->getContainer()->get('event_dispatcher');
        $workerActivity = $this->getContainer()->get('dp.voice.worker_activity');

        $startTime = time();
        $lastHash  = null;

        while (true) {
            try {
                $workers = $workerActivity->getActiveWorkers();
                $data    = $serializer->toArray($workers);
                $hash    = md5(serialize($data));

                if ($lastHash !== $hash) {
                    $dispatcher->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent.voice.online-status', [
                            'online_status' => $data,
                        ])
                    );

                    $lastHash = $hash;
                }
            } catch (\Exception $e) {
            }

            sleep(5);

            // offset + overlap interval
            if ((time() - $startTime) > self::DEFAULT_INTERVAL + 180) {
                break;
            }
        }
    }
}
