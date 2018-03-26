<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Facebook\FacebookApi;
use Application\DeskPRO\JobQueue\JobQueue;
use Doctrine\DBAL\Connection;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Processes outgoing Facebook Page Feed jobs.
 *
 * The job data contains facebook page/app auth info, the message, and the parent comment, and is considered a
 * "dumb" or standalone job.
 *
 * The processor will automatically attempt to send it 5 times before giving up completely.
 */
class OutgoingFacebookFeedProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'outgoing_facebook_feed';

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
            'app_id',
            'app_secret',
            'page_token',
            'replying_to_id',
        ]);

        $resolver->setDefaults([]);
    }

    public function process(array $data, array $job)
    {
        $facebookApi = new FacebookApi(null, $data['app_id'], $data['app_secret']);
        try {
            $facebookApi->commentOnPost($data['replying_to_id'], $data['message'], $data['page_token']);
        } catch (\InvalidArgumentException $e) {
            $this->markRejected($job, 'could not send facebook reply', '', Job::STATUS_CODE_INVALID_DATA);

            return false;
        }

        return true;
    }

    public function runExceptionHandler(array $job, \Exception $e)
    {
        $this->markExceptionError(
            $job,
            $this->willRetry($job) ? Job::STATUS_CODE_RETRYING : Job::STATUS_CODE_EXHAUSTED,
            sprintf('Facebook Comment Failed (%s/5 attempts)', $job['num_tries'] + 1), // num_tries starts at 0
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
