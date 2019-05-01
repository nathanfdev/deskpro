<?php

namespace DeskPRO\Bundle\VoiceBundle;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;

/**
 * Interface VoiceProviderInterface.
 */
interface VoiceProviderInterface
{
    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $toNumber
     * @param array          $options
     * @param mixed          $exception
     *
     * @return string|false Returns call uuid on success
     */
    public function callNumber(VoicePhoneCall $phoneCall, $toNumber, array $options = [], &$exception = false);

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return string|false Returns call uuid on success
     */
    public function callForwardingNumber(VoicePhoneCall $phoneCall, Person $agent);

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     */
    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $agent);

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function endCall(VoicePhoneCall $phoneCall);

    /**
     * @param AbstractVoicePhoneCallParticipant $participant
     */
    public function kickParticipant(AbstractVoicePhoneCallParticipant $participant);

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    public function holdEndUser(VoicePhoneCall $phoneCall, $isHold);

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $callSid
     * @param bool           $mute
     *
     * @throws \Exception
     */
    public function muteParticipant(VoicePhoneCall $phoneCall, $callSid, $mute);

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return bool
     */
    public function isCallActive(VoicePhoneCall $phoneCall);

    /**
     * @param AbstractVoicePhoneCallParticipant $participant
     * @param string                            $callbackUrl
     * @param string                            $callbackMethod
     *
     * @return bool
     */
    public function transferParticipant(AbstractVoicePhoneCallParticipant $participant, $callbackUrl, $callbackMethod);

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function prepareForColdTransfer(VoicePhoneCall $phoneCall);
}
