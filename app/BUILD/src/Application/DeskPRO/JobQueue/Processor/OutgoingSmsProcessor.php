<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\Sms\SmsProviderFactory;
use Doctrine\DBAL\Connection;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsSender;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Processes an outgoing SMS message.
 *
 * The job data contains provider auth details, the message, and to to/from number, and is considered a
 * "dumb" or standalone job.
 *
 * The processor will automatically attempt to send it 5 times before giving up completely.
 */
class OutgoingSmsProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'outgoing_sms';

    /**
     * @var JobQueue
     */
    private $queue;

    public function __construct(Connection $connection, JobQueue $queue)
    {
        parent::__construct($connection);
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'message',
            'to_number',
            'provider',
        ]);

        $resolver->setDefaults([
            'from_number'     => null,
            'provider_params' => [],
        ]);
    }

    public function process(array $data, array $job)
    {
        try {
            $provider = SmsProviderFactory::create($data['provider'], $data['provider_params']);
        } catch (\InvalidArgumentException $e) {
            $this->markRejected($job, 'sms provider not found', '', Job::STATUS_CODE_INVALID_DATA);

            return false;
        }
        $sender = new SmsSender($provider, $data['from_number']);

        $message = new SmsMessage($data['message']);
        $sender->send($data['to_number'], $message);

        return true;
    }

    public function runExceptionHandler(array $job, \Exception $e)
    {
        $this->markExceptionError(
            $job,
            $this->willRetry($job) ? Job::STATUS_CODE_RETRYING : Job::STATUS_CODE_EXHAUSTED,
            sprintf('SMS Failed (%s/5 attempts)', $job['num_tries'] + 1), // num_tries starts at 0
            $e
        );

        if ($this->willRetry($job)) {
            $this->queue->retryByJobId($job['id'], new \DateTime('now + 2 minutes'));
        }
    }

    private function willRetry($job)
    {
        return $job['num_tries'] < 5;
    }
}
