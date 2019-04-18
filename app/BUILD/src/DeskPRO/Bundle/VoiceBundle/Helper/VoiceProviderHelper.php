<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\TaskRouter;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use DeskPRO\Bundle\VoiceBundle\VoiceProviderInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class VoiceProviderHelper.
 */
class VoiceProviderHelper implements VoiceProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var TaskRouter
     */
    private $taskRouter;

    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var PlivoAdapter
     */
    private $plivoAdapter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param UrlGeneratorInterface    $router
     * @param TaskRouter               $taskRouter
     * @param TwilioAdapter            $twilioAdapter
     * @param PlivoAdapter             $plivoAdapter
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager            $em,
        UrlGeneratorInterface    $router,
        TaskRouter               $taskRouter,
        TwilioAdapter            $twilioAdapter,
        PlivoAdapter             $plivoAdapter,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em            = $em;
        $this->router        = $router;
        $this->taskRouter    = $taskRouter;
        $this->twilioAdapter = $twilioAdapter;
        $this->plivoAdapter  = $plivoAdapter;
        $this->dispatcher    = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function callNumber(VoicePhoneCall $phoneCall, $toNumber, array $options = [], &$exception = false)
    {
        return $this->getAdapter($phoneCall)->callNumber($phoneCall, $toNumber, $options, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function callForwardingNumber(VoicePhoneCall $phoneCall, Person $agent)
    {
        return $this->getAdapter($phoneCall)->callForwardingNumber($phoneCall, $agent);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        $this->getAdapter($phoneCall)->cancelForwardingCall($phoneCall, $agent);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function endCall(VoicePhoneCall $phoneCall)
    {
        $this->getAdapter($phoneCall)->endCall($phoneCall);

        // force end all agent workers
        // in case if agent hangup callback is not called for some reason
        foreach ($phoneCall->getAgentParticipants() as $participant) {
            foreach ($phoneCall->getTaskSids() as $taskSid) {
                $this->taskRouter->completeTaskForWorker(
                    $taskSid,
                    'agent',
                    $participant->getPerson()->getId()
                );
            }
        }

        if (!$phoneCall->isVoicemail()) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
            $this->em->flush();
        }

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.call-ended', [
                'call_id' => $phoneCall->getId(),
            ])
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function tryEndCallByAgent(VoicePhoneCall $phoneCall)
    {
        if (count($phoneCall->getActiveParticipants()) < 2) {
            $this->endCall($phoneCall);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function kickParticipant(AbstractVoicePhoneCallParticipant $participant)
    {
        $this->getAdapter($participant->getPhoneCall())->kickParticipant($participant);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function holdEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $phoneCall->getUserParticipants()->map(function (AbstractVoicePhoneCallParticipant $participant) use ($isHold) {
            $participant->setOnHold($isHold);
        });

        $this->em->flush();
        $this->getAdapter($phoneCall)->holdEndUser($phoneCall, $isHold);

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.hold',
            [
                'call_id' => $phoneCall->getId(),
                'hold'    => $isHold,
            ]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function muteParticipant(VoicePhoneCall $phoneCall, $callSid, $mute)
    {
        $this->getAdapter($phoneCall)->muteParticipant($phoneCall, $callSid, $mute);
    }

    /**
     * {@inheritdoc}
     */
    public function isCallActive(VoicePhoneCall $phoneCall)
    {
        return $this->getAdapter($phoneCall)->isCallActive($phoneCall);
    }

    /**
     * {@inheritdoc}
     */
    public function transferUser(VoicePhoneCall $phoneCall, $callbackUrl, $callbackMethod)
    {
        foreach ($phoneCall->getUserParticipants() as $participant) {
            $this->getAdapter($phoneCall)->transferParticipant($participant, $callbackUrl, $callbackMethod);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transferParticipant(AbstractVoicePhoneCallParticipant $participant, $callbackUrl, $callbackMethod)
    {
        return $this->getAdapter($participant->getPhoneCall())->transferParticipant($participant, $callbackUrl, $callbackMethod);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function prepareForColdTransfer(VoicePhoneCall $phoneCall)
    {
        $this->getAdapter($phoneCall)->prepareForColdTransfer($phoneCall);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @throws OutOfServiceException
     *
     * @return PlivoAdapter|TwilioAdapter
     */
    private function getAdapter(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if ($account instanceof TwilioVoiceAccount) {
            return $this->twilioAdapter;
        } elseif ($account instanceof PlivoVoiceAccount) {
            return $this->plivoAdapter;
        }

        throw new OutOfServiceException();
    }
}
