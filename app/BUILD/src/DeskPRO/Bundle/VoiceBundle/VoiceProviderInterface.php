<?php

namespace DeskPRO\Bundle\VoiceBundle;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;

/**
 * Interface VoiceProviderInterface.
 */
interface VoiceProviderInterface
{
    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     */
    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $agent);

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function cancelForwardingCalls(VoicePhoneCall $phoneCall);

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function tryEndConference(VoicePhoneCall $phoneCall);

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold);

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
     * @throws \Exception
     *
     * @return Person[]
     */
    public function getActivePhoneCallParticipants(VoicePhoneCall $phoneCall);

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isConferenceOnHold(VoicePhoneCall $phoneCall);

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     */
    public function cancelCall(VoicePhoneCall $phoneCall);

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return bool
     */
    public function isCallActive(VoicePhoneCall $phoneCall);

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $callbackUrl
     * @param string         $callbackMethod
     *
     * @return bool
     */
    public function transferCall(VoicePhoneCall $phoneCall, $callbackUrl, $callbackMethod);
}
