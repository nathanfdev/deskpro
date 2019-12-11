<?php

namespace DeskPRO\Bundle\VoiceBundle;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;

class NotImplementedVoiceProvider implements VoiceProviderInterface
{
    public function callNumber(VoicePhoneCall $phoneCall, $toNumber, array $options = [], &$exception = false)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function callForwardingNumber(VoicePhoneCall $phoneCall, Person $agent)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function endCall(VoicePhoneCall $phoneCall)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function kickParticipant(AbstractVoicePhoneCallParticipant $participant)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function holdEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function isCallActive(VoicePhoneCall $phoneCall)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function transferParticipant(AbstractVoicePhoneCallParticipant $participant, $callbackUrl, $callbackMethod)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function prepareForColdTransfer(VoicePhoneCall $phoneCall)
    {
        throw new \RuntimeException("Not implemented");
    }

    public function deleteRecording(VoicePhoneCall $phoneCall, $recordingSid)
    {
        throw new \RuntimeException("Not implemented");
    }
}
