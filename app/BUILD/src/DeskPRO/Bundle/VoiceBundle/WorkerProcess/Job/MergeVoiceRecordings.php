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
        $em         = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $phoneCalls = $em->getRepository(VoicePhoneCall::class)->findBy(
            [
                'fullRecording' => null,
            ],
            null,
            50
        );

        foreach ($phoneCalls as $phoneCall) {
            /** @var VoicePhoneCall $phoneCall */
            $allDone = true;
            foreach ($phoneCall->getRecordings() as $recording) {
                $allDone = $allDone && $recording->getBlob();
            }
            if ($allDone) {
                $newRecording = $this->mergeRecordings($phoneCall);

                $context = new SideloadSerializationContext();
                $context->setIncludes(['recording_enabled']);
                $context->setInlineSideloads(true);

                $serializedData = $this->getContainer()->get('serializer')->toArray(new ApiWrapper($newRecording->getPhoneCall()), $context);

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

    private function mergeRecordings(VoicePhoneCall $phoneCall)
    {
        $em          = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $blobStorage = $this->getContainer()->get('blob.storage');

        $newAudioFile = null;
        $parser       = new Parser();
        $duration     = 0;
        $newRecording = new VoiceRecording();

        foreach ($phoneCall->getRecordings() as $rec) {
            $blobString = $blobStorage->copyBlobRecordToString($rec->getBlob());
            if (!$newAudioFile) {
                $newAudioFile = $parser->parseString($blobString);
            } else {
                /* @var AudioFile $newAudioFile */
                $newAudioFile->append($parser->parseString($blobString));
            }

            $duration += $rec->getDuration();
            $newRecording->addVoiceRecordingMetadata($rec);
            $newRecording->setTranscription($newRecording->getTranscription() ?: ''.$rec->getTranscription() ? "\r\n\r\n".$rec->getTranscription() : '');
            $em->remove($rec);
        }
        $newBlobString = IO::saveAudioToMemory($newAudioFile);
        $newBlob       = $blobStorage->createBlobRecordFromString(
            $newBlobString,
            'call_record_'.$phoneCall->getId().'_merged.wav',
            'wav'
        );
        $em->persist($newBlob);

        $newRecording
            ->setDuration($duration)
            ->setBlob($newBlob)
            ->setPhoneCall($phoneCall);
        $phoneCall->setFullRecording($newRecording);
        $em->persist($newRecording);
        $em->flush();

        return $newRecording;
    }
}
