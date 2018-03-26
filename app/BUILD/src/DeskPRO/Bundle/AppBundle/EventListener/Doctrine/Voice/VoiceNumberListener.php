<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;

/**
 * Class VoiceNumberListener.
 */
class VoiceNumberListener
{
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
     * @param VoiceNumber $number
     */
    public function onCreate(VoiceNumber $number)
    {
        $account = $number->getAccount();
        if (!$account) {
            return;
        }

        // link number to the DeskPRO app
        $this->twilioAdapter->updateNumber($number, [
            'voiceApplicationSid' => $account->getTwimlAppSid(),
        ]);

        // send refresh alert
        $this->notifyAgents();
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param VoiceNumber        $number
     * @param PreUpdateEventArgs $args
     */
    public function updateWorker(VoiceNumber $number, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('outboundCallsEnabled')) {
            // send refresh alert
            $this->notifyAgents();
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param VoiceNumber $number
     *
     * @throws \Exception
     */
    public function onRemove(VoiceNumber $number)
    {
        // unlink number from twilio
        try {
            $this->twilioAdapter->updateNumber($number, [
                'voiceApplicationSid' => '',
            ]);
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }

        // send refresh alert
        $this->notifyAgents();
    }

    /**
     * Send agent alert to refresh the agent interface.
     */
    private function notifyAgents()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('a, p')
            ->from(AgentData::class, 'a')
            ->join('a.person', 'p')
            ->where(
                'a.isVoiceEnabled = 1',
                'a.outboundCallsEnabled = 1'
            )
        ;

        /** @var AgentData[] $agents */
        $agents = $qb->getQuery()->getResult();
        foreach ($agents as $agentData) {
            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
                'target'      => $agentData->getPerson()->getId(),
            ]));
        }
    }
}
