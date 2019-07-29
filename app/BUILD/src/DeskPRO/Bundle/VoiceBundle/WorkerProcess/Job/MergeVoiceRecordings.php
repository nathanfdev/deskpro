<?php

namespace DeskPRO\Bundle\VoiceBundle\WorkerProcess\Job;

use Application\DeskPRO\WorkerProcess\Job\AbstractJob;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceRecording;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Component\Util\Audio\Wav\AudioFile;
use DeskPRO\Component\Util\Audio\Wav\IO;
use DeskPRO\Component\Util\Audio\Wav\Parser;

/**
 * Class RunTaskRouter.
 */
class MergeVoiceRecordings extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function run()
    {
        $serializer = $this->getContainer()->get('serializer');
        $em         = $this->getContainer()->get('doctrine.orm.entity_manager');

        $qb = $em
            ->createQueryBuilder()
            ->select('p')
            ->from(VoicePhoneCall::class, 'p')
            ->leftJoin('p.fullRecording', 'fr')
            ->join('p.recordings', 'r')
            ->where('p.fullRecording IS NULL OR fr.blob IS NULL')
            ->andWhere('p.status IN (:ended_statuses)')
            ->andWhere('p.dateEnded < :date_offset')
            ->groupBy('p.id')
            ->having('COUNT(r.id) > 0')
            ->setParameter('ended_statuses', [VoicePhoneCall::STATUS_ENDED, VoicePhoneCall::STATUS_VOICEMAIL])
            ->setParameter('date_offset', new \DateTime('-1 min'))
            ->setMaxResults(50)
        ;

        /** @var VoicePhoneCall[] $phoneCalls */
        $phoneCalls = $qb->getQuery()->getResult();
        foreach ($phoneCalls as $phoneCall) {
            $loaded = true;
            foreach ($phoneCall->getTempRecordings() as $recording) {
                $loaded = $loaded && $recording->getBlob();
            }

            if ($loaded) {
                $this->mergeRecordings($phoneCall);

                $context = new SideloadSerializationContext();
                $context->setIncludes(['recording_enabled']);
                $context->setInlineSideloads(true);

                $serializedData = $serializer->toArray(new ApiWrapper($phoneCall), $context);
                $this->getContainer()->get('event_dispatcher')->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent(
                        'agent.voice.recording_status',
                        ['data' => $serializedData]
                    )
                );
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    private function mergeRecordings(VoicePhoneCall $phoneCall)
    {
        $em          = $this->getContainer()->get('doctrine.orm.entity_manager');
        $blobStorage = $this->getContainer()->get('blob.storage');

        $newAudioFile = null;
        $parser       = new Parser();
        $duration     = 0;

        $fullRecording = $phoneCall->getFullRecording() ?: new VoiceRecording();

        foreach ($phoneCall->getTempRecordings() as $recording) {
            $blobString = $blobStorage->copyBlobRecordToString($recording->getBlob());
            if (!$newAudioFile) {
                $newAudioFile = $parser->parseString($blobString);
            } else {
                /* @var AudioFile $newAudioFile */
                $newAudioFile->append($parser->parseString($blobString));
            }

            $duration += $recording->getDuration();
            $fullRecording->addVoiceRecordingMetadata($recording);
            $fullRecording->setTranscription($fullRecording->getTranscription() ?: ''.$recording->getTranscription() ? "\r\n\r\n".$recording->getTranscription() : '');
            $em->remove($recording);
        }

        $newBlobString = IO::saveAudioToMemory($newAudioFile);
        $newBlob       = $blobStorage->createBlobRecordFromString(
            $newBlobString,
            'call_record_'.$phoneCall->getId().'_merged.wav',
            'wav'
        );

        $em->persist($newBlob);

        $fullRecording
            ->setDuration($duration)
            ->setBlob($newBlob)
            ->setPhoneCall($phoneCall)
        ;

        $phoneCall->setFullRecording($fullRecording);
        $em->persist($fullRecording);
        $em->flush();
    }
}
