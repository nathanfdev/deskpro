<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\TicketLog;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
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
        $callId = $data['call_id'];

        try {
            /** @var VoicePhoneCall $phoneCall */
            $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
            if (!$phoneCall) {
                throw new \Exception('Phone call not found');
            }

            $blob = $this->getBlob('call_record_'.$callId, $phoneCall->getData());
            $phoneCall->setRecording($blob);

            $this->em->persist($phoneCall);
            $this->em->flush();

            if ($phoneCall->getVoicemailRecord()) {
                // send voicemail notification
                $serializedData = $this->serializer->toArray(
                    new ApiWrapper($phoneCall->getVoicemailRecord()),
                    new SideloadSerializationContext([
                        'voice_phone_call',
                        'person',
                    ])
                );

                $this->eventDispatcher->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent('agent.voice.voicemail.new-message', [
                        'data'   => $serializedData,
                        'target' => $phoneCall->getVoicemailRecord()->getAgent()->getId(),
                    ])
                );
            } else {
                $serializedData = $this->serializer->toArray(
                    new ApiWrapper($phoneCall),
                    new SideloadSerializationContext()
                );

                $this->eventDispatcher->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent(
                        'agent.voice.recording_status',
                        ['data' => $serializedData]
                    )
                );
            }

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
                    ->setDetails(['duration' => $phoneCall->getDuration()])
                ;

                $this->em->persist($ticketLog);
            }

            $this->em->persist($phoneCall);
            $this->em->flush();

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
        $resolver->setRequired('call_id');
    }

    /**
     * @param string $filename
     * @param array  $callData
     *
     * @return \Application\DeskPRO\Entity\Blob
     */
    private function getBlob($filename, array $callData)
    {
        $client = new Client();
        $data   = $client->send(new Request('GET', $callData['RecordingUrl']))->getBody()->getContents();
        $blob   = $this->blobStorage->createBlobRecordFromString($data, $filename.'.wav', 'wav');

        return $blob;
    }
}
