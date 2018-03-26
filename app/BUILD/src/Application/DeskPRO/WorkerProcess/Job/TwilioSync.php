<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;

/**
 * Class TwilioSync.
 */
class TwilioSync extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $em      = $this->getContainer()->getEm();
        $account = $em->getRepository(VoiceAccount::class)->getVoiceAccount();

        // no account
        if (!$account) {
            return;
        }

        // no sync required
        if (!$account->getDateSync()) {
            return;
        }

        // already synced
        if ($account->getDateLastSync() && $account->getDateSync() == $account->getDateLastSync()) {
            return;
        }

        // sync twilio account
        $this->getContainer()->get('twilio_sync_manager')->syncAccount($account);

        $account->setDateLastSync($account->getDateSync());
        $em->persist($account);
        $em->flush();
    }
}
