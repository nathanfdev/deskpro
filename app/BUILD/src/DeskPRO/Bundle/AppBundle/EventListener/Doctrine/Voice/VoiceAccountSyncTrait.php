<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use Doctrine\ORM\EntityManager;

/**
 * Class VoiceAccountSyncTrait.
 *
 * @property EntityManager $em
 */
trait VoiceAccountSyncTrait
{
    /**
     * @var bool
     */
    protected $allowSync = true;

    /**
     * @param bool $allowSync
     */
    public function setAllowSync($allowSync)
    {
        $this->allowSync = $allowSync;
    }

    /**
     * @param VoiceAccount $account
     */
    protected function updateAccountDateSync(VoiceAccount $account)
    {
        if (!$this->allowSync) {
            return;
        }

        $this->em->getConnection()->update(
            'voice_accounts',
            [
                'date_sync' => date('c'),
            ],
            [
                'id' => $account->getId(),
            ]
        );
    }
}
