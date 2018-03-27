<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class VoiceQueueListener.
 */
class VoiceQueueListener
{
    use VoiceAccountSyncTrait;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param VoiceQueue $queue
     */
    public function onPersist(VoiceQueue $queue)
    {
        $account = $queue->getAccount();
        if (!$account) {
            return;
        }

        $this->updateAccountDateSync($account);
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param VoiceQueue         $queue
     * @param PreUpdateEventArgs $args
     */
    public function onUpdate(VoiceQueue $queue, PreUpdateEventArgs $args)
    {
        $account = $queue->getAccount();
        if (!$account) {
            return;
        }

        $agents = $queue->getAgents();

        if ($args->hasChangedField('agents')
            || ($agents instanceof PersistentCollection && $agents->isDirty())
            || $args->hasChangedField('routingModel')
            || $args->hasChangedField('maxQueueSize')
            || $args->hasChangedField('voicemailTimeout')) {
            $this->updateAccountDateSync($account);
        }
    }
}
