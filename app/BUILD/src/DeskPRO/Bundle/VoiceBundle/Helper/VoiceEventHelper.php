<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VoiceEventHelper.
 */
class VoiceEventHelper
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param Serializer               $serializer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Serializer $serializer, EventDispatcherInterface $dispatcher)
    {
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     */
    public function sendConferenceStatus(VoicePhoneCall $phoneCall)
    {
        // send client message
        // for real time ui updates
        $statusParams = [];

        // phone call
        $context = new SideloadSerializationContext();
        $context->setIncludes(['recording_enabled']);
        $context->setInlineSideloads(true);

        $statusParams['phone_call'] = $this->serializer->toArray(new ApiWrapper($phoneCall), $context)['data'];
        unset($statusParams['phone_call']['ticket']);

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.conference.status', $statusParams)
        );
    }
}
