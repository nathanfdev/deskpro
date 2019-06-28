<?php

namespace DeskPRO\Bundle\VoiceBundle\WorkerProcess\Job;

use Application\DeskPRO\WorkerProcess\Job\AbstractJob;

/**
 * Class RunTaskRouter.
 */
class RunTaskRouter extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function run()
    {
        if (!$this->getContainer()->get('deskpro.feature_flags')->hasVoice()) {
            return;
        }

        $startTime  = time();
        $taskRouter = $this->getContainer()->get('dp.voice.task_router');

        while (true) {
            // evaluate task router
            try {
                $taskRouter->evaluate();
            } catch (\Exception $e) {
            }

            sleep(2);

            // offset + overlap interval
            if ((time() - $startTime) > self::DEFAULT_INTERVAL + 60) {
                break;
            }
        }
    }
}
