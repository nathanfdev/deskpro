<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
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
     * @param TwilioAdapter            $twilioAdapter
     * @param PlivoAdapter             $plivoAdapter
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager            $em,
        UrlGeneratorInterface    $router,
        TwilioAdapter            $twilioAdapter,
        PlivoAdapter             $plivoAdapter,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em            = $em;
        $this->router        = $router;
        $this->twilioAdapter = $twilioAdapter;
        $this->plivoAdapter  = $plivoAdapter;
        $this->dispatcher    = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        $this->getAdapter($phoneCall)->cancelForwardingCall($phoneCall, $agent);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelForwardingCalls(VoicePhoneCall $phoneCall)
    {
        $this->getAdapter($phoneCall)->cancelForwardingCalls($phoneCall);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOutgoingCalls(VoicePhoneCall $phoneCall)
    {
        $this->getAdapter($phoneCall)->cancelOutgoingCalls($phoneCall);
    }

    /**
     * {@inheritdoc}
     */
    public function tryEndConference(VoicePhoneCall $phoneCall)
    {
        $ended = $this->getAdapter($phoneCall)->tryEndConference($phoneCall);
        if ($ended) {
            $this->cancelOutgoingCalls($phoneCall);

            if (!$phoneCall->isVoicemail()) {
                $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
                $this->em->flush($phoneCall);
            }

            $this->dispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent.voice.call-ended', [
                    'call_id' => $phoneCall->getId(),
                ])
            );
        }

        return $ended;
    }

    /**
     * {@inheritdoc}
     */
    public function endConference(VoicePhoneCall $phoneCall)
    {
        $this->getAdapter($phoneCall)->endConference($phoneCall);
        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.call-ended', [
                'call_id' => $phoneCall->getId(),
            ])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold, array $params = [])
    {
        $adapter = $this->getAdapter($phoneCall);
        if ($adapter instanceof PlivoAdapter) {
            $account = $phoneCall->getNumber()->getAccount();
            if (!$account || !$account instanceof PlivoVoiceAccount) {
                throw new \RuntimeException('Voice number does not have an account reference.');
            }

            $params['holdUrl'] = $this->router->generate('plivo_user_put_on_hold_callback', [
                'account'     => $account->getId(),
                'accountAuth' => $account->getAccountAuth(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $params['joinUrl'] = $this->router->generate('plivo_user_joins_conference_callback', [
                'account'     => $account->getId(),
                'accountAuth' => $account->getAccountAuth(),
                'callId'      => $phoneCall->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->getAdapter($phoneCall)->holdConferenceEndUser($phoneCall, $isHold, $params);
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
    public function getActivePhoneCallParticipants(VoicePhoneCall $phoneCall)
    {
        return $this->getAdapter($phoneCall)->getActivePhoneCallParticipants($phoneCall);
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
    public function cancelCall(VoicePhoneCall $phoneCall)
    {
        $this->getAdapter($phoneCall)->cancelCall($phoneCall);
    }

    /**
     * {@inheritdoc}
     */
    public function transferCall(VoicePhoneCall $phoneCall, $callbackUrl, $callbackMethod)
    {
        return $this->getAdapter($phoneCall)->transferCall($phoneCall, $callbackUrl, $callbackMethod);
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
