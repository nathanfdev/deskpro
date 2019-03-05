<?php

namespace DeskPRO\Bundle\VoiceBundle\JobQueue\Processor;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceRecording;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicemailAgentRecording;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Entity\VoiceRecording;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceDownloadRecordProcessor.
 */
class VoiceDownloadRecordProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'voice_call_record_download';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param Connection               $connection
     * @param EntityManager            $em
     * @param DeskproBlobStorage       $blobStorage
     * @param Serializer               $serializer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Connection               $connection,
        EntityManager            $em,
        DeskproBlobStorage       $blobStorage,
        Serializer               $serializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($connection);

        $this->em              = $em;
        $this->blobStorage     = $blobStorage;
        $this->serializer      = $serializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        try {
            $recording = null;

            if (isset($data['recording_id'])) {
                /** @var VoiceRecording $recording */
                $recording = $this->em->getRepository(VoiceRecording::class)->find($data['recording_id']);
                if (!$recording) {
                    return;
                }

                $blob = $this->getBlob('call_record_'.$data['recording_id'], $recording);
                $recording->setBlob($blob);

                $this->em->persist($recording);
                $this->em->flush();

                $serializedData = $this->serializer->toArray(
                    new ApiWrapper($recording->getPhoneCall()),
                    new SideloadSerializationContext()
                );

                $this->eventDispatcher->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent(
                        'agent.voice.recording_status',
                        ['data' => $serializedData]
                    )
                );
            } elseif (isset($data['voicemail_recording_id'])) {
                /** @var VoicemailAgentRecording $recording */
                $recording = $this->em->getRepository(VoicemailAgentRecording::class)->find($data['voicemail_recording_id']);
                if (!$recording) {
                    return;
                }

                $blob = $this->getBlob('call_record_'.$data['voicemail_recording_id'], $recording);
                $recording->setBlob($blob);

                $this->em->persist($recording);
                $this->em->flush();

                // send voicemail notification
                $serializedData = $this->serializer->toArray(
                    new ApiWrapper($recording),
                    new SideloadSerializationContext([
                        'voice_phone_call',
                        'person',
                    ])
                );

                $this->eventDispatcher->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent('agent.voice.voicemail.new-message', [
                        'data'   => $serializedData,
                        'target' => $recording->getAgent()->getId(),
                    ])
                );
            }

            if ($recording instanceof AbstractVoiceRecording) {
                $phoneCall = $recording->getPhoneCall();

                // log conference start event
                $log = new VoicePhoneCallLog();
                $log->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DOWNLOADED);
                $log->setPhoneCall($phoneCall);

                $messageAttribute = $this->em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
                    'phoneCall' => $phoneCall,
                ]);

                if ($messageAttribute) {
                    $ticketLog = new TicketLog();
                    $ticketLog
                        ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DOWNLOADED)
                        ->setTicket($messageAttribute->getMessage()->getTicket())
                        ->setIdObject($phoneCall->getId())
                        ->setDetails(['duration' => $recording->getDuration()])
                    ;

                    $this->em->persist($ticketLog);
                }

                $this->em->persist($phoneCall);
                $this->em->flush();
            }

            $this->runSuccessHandler($job);
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['recording_id', 'voicemail_recording_id']);
    }

    /**
     * @param string                 $filename
     * @param AbstractVoiceRecording $recording
     *
     * @return \Application\DeskPRO\Entity\Blob
     */
    private function getBlob($filename, AbstractVoiceRecording $recording)
    {
        $client = new Client();
        $data   = $client->send(new Request('GET', $recording->getRecordingUrl()))->getBody()->getContents();
        $blob   = $this->blobStorage->createBlobRecordFromString($data, $filename.'.wav', 'wav');

        return $blob;
    }
}
