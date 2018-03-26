<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VoiceWorkerListener.
 */
class VoiceWorkerListener
{
    use VoiceAccountSyncTrait;

    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param TwilioAdapter            $twilioAdapter
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(TwilioAdapter $twilioAdapter, EntityManager $em, EventDispatcherInterface $dispatcher)
    {
        $this->twilioAdapter = $twilioAdapter;
        $this->em            = $em;
        $this->dispatcher    = $dispatcher;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param AgentData $agentData
     */
    public function createWorker(AgentData $agentData)
    {
        // voice disabled
        if (!$agentData->isVoiceEnabled()) {
            return;
        }

        // already created
        if ($agentData->getVoiceWorkerSid()) {
            return;
        }

        $agentData->setAvailableStatus(AgentData::AVAILABLE_STATUS_OFFLINE);
        $this->updateAccountDateSync($this->getVoiceAccount());

        if ($agentData->getPerson()) {
            // force reload agent's interface to show voice UI components with a spinner before real sync
            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
                'target'      => $agentData->getPerson()->getId(),
            ]));
        }
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param AgentData          $agentData
     * @param PreUpdateEventArgs $args
     */
    public function updateWorker(AgentData $agentData, PreUpdateEventArgs $args)
    {
        $account = $this->getVoiceAccount();
        if (!$account) {
            return;
        }

        if ($args->hasChangedField('isVoiceEnabled')) {
            // create or delete worker
            if ($agentData->isVoiceEnabled()) {
                $this->createWorker($agentData);
            } else {
                $this->deleteWorker($agentData);
            }
        } elseif ($args->hasChangedField('availableStatus') || $args->hasChangedField('agentCallsEnabled')) {
            // change activity
            if ($agentData->getVoiceWorkerSid()) {
                $activity = TwilioAdapter::getActivityStatus($agentData);
                $this->twilioAdapter->updateAgentWorker($account, $agentData->getPerson(), $activity);
            }
        } elseif ($args->hasChangedField('outboundCallsEnabled') || $args->hasChangedField('canUseForwarding')) {
            // force reload agent's interface to show/hide outbound dialpad
            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
                'target'      => $agentData->getPerson()->getId(),
            ]));
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param AgentData $agentData
     */
    public function deleteWorker(AgentData $agentData)
    {
        // no worker
        if (!$agentData->getVoiceWorkerSid()) {
            return;
        }

        $account = $this->getVoiceAccount();
        if (!$account) {
            return;
        }

        // unset voice data
        $agentData->setVoiceWorkerSid(null);
        $agentData->setVoiceTaskQueueSid(null);
        $agentData->setExtensionNumber(null);

        // remove agent from queues
        $this->em->getConnection()->executeQuery('DELETE FROM voice_queue_agents WHERE agent_id = :person_id', [
            'person_id' => $agentData->getPerson()->getId(),
        ]);

        // remove specific agent targets
        $this->em->getConnection()->executeQuery('DELETE FROM voice_targets WHERE agent_id = :person_id', [
            'person_id' => $agentData->getPerson()->getId(),
        ]);

        // force reload agent's interface to hide voice UI components before real sync
        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
            'type'        => 'admin',
            'person_id'   => 0,
            'person_name' => 'System',
            'target'      => $agentData->getPerson()->getId(),
        ]));

        $this->updateAccountDateSync($this->getVoiceAccount());
    }

    /**
     * @return VoiceAccount|null
     */
    private function getVoiceAccount()
    {
        return $this->em->getRepository(VoiceAccount::class)->getVoiceAccount();
    }
}
