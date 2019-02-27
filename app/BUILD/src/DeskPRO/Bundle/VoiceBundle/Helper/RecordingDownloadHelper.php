<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\JobQueue\Processor\VoiceDownloadRecordProcessor;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RecordingDownloadHelper.
 */
class RecordingDownloadHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var JobQueue
     */
    private $jobQueue;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param Serializer               $serializer
     * @param EventDispatcherInterface $dispatcher
     * @param JobQueue                 $jobQueue
     */
    public function __construct(EntityManager $em, Serializer $serializer, EventDispatcherInterface $dispatcher, JobQueue $jobQueue)
    {
        $this->em         = $em;
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
        $this->jobQueue   = $jobQueue;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $recordingUrl
     * @param string         $duration
     */
    public function enqueueRecordingDownload(VoicePhoneCall $phoneCall, $recordingUrl, $duration)
    {
        $recordingEnabled = true;
        if ($phoneCall->getQueue()) {
            $recordingEnabled = $phoneCall->getQueue()->isRecordingEnabled();
        }

        $phoneCall->setDuration($duration);
        $phoneCall->setData(array_merge($phoneCall->getData(), [
            'RecordingUrl'     => $recordingUrl,
            'RecordingEnabled' => $recordingEnabled,
        ]));

        $this->em->persist($phoneCall);
        $this->em->flush();

        if ($recordingEnabled) {
            $this->jobQueue->addJob(new Job(VoiceDownloadRecordProcessor::JOB_TYPE, [
                'call_id' => $phoneCall->getId(),
            ]));
        }

        $serializedData = $this->serializer->toArray(
            new ApiWrapper($phoneCall),
            new SideloadSerializationContext()
        );

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent(
                'agent.voice.recording_status',
                ['data' => $serializedData]
            )
        );
    }
}
