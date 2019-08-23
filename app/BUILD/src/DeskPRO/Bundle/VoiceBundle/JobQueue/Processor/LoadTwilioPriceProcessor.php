<?php

namespace DeskPRO\Bundle\VoiceBundle\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LoadTwilioPriceProcessor.
 */
class LoadTwilioPriceProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'voice_load_twilio_price';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var JobQueue
     */
    private $jobQueue;

    /**
     * Constructor.
     *
     * @param Connection    $connection
     * @param EntityManager $em
     * @param TwilioAdapter $twilioAdapter
     * @param JobQueue      $jobQueue
     */
    public function __construct(Connection $connection, EntityManager $em, TwilioAdapter $twilioAdapter, JobQueue $jobQueue)
    {
        parent::__construct($connection);

        $this->em            = $em;
        $this->twilioAdapter = $twilioAdapter;
        $this->jobQueue      = $jobQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        try {
            $account = $this->em->getRepository(TwilioVoiceAccount::class)->find($data['account_id']);
            if (!$account) {
                throw new \RuntimeException("Voice account {$data['account_id']} not found");
            }

            $callInfo = $this->twilioAdapter->getCallInfo($account, $data['call_sid']);
            if ($callInfo && $callInfo->price) {
                $this->jobQueue->addJob(new Job(VoiceCallCostProcessor::JOB_TYPE, [
                    'call_sid' => $data['call_sid'],
                    'cost'     => preg_replace('/^-/', '', $callInfo->price),
                    'currency' => $callInfo->priceUnit,
                ]));

                $this->runSuccessHandler($job);
            } else {
                $this->jobQueue->retryByJobId($job['id'], new \DateTime('+5 minutes'));
            }
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['call_id', 'call_sid', 'account_id']);
    }
}
