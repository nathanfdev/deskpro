<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
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
     * Constructor.
     *
     * @param Connection         $connection
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(Connection $connection, EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        parent::__construct($connection);

        $this->em          = $em;
        $this->blobStorage = $blobStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        $client = new Client();

        try {
            $callId    = $data['call_id'];
            $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
            $callData  = $phoneCall->getData();

            if (!$phoneCall) {
                throw new \Exception('Phone call not found');
            }

            $data = base64_encode($client->send(new Request('GET', $callData['RecordingUrl']))->getBody()->getContents());
            $blob = $this->blobStorage->createBlobRecordFromString($data, 'call_record_'.$callId, 'mp3');

            $phoneCall->setRecording($blob);

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
}
