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

        try {
            $this->getContainer()->get('dp.voice.task_router')->evaluate();
        } catch (\Exception $e) {
        }
    }
}
