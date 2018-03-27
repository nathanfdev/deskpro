<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobProcessorInterface;
use Application\DeskPRO\JobQueue\JobQueueException;
use Doctrine\DBAL\Connection;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionsResolverException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Helper methods available to children, encouraged to extend this when creating a job processor (but not required to).
 *
 * AbstractJobProcessor's need to implement process() for its logic, and getDataOptions() to set expectations of payload
 *
 * Please see the JOB_TYPE constant and the canHandle() method of this class
 */
abstract class AbstractJobProcessor implements JobProcessorInterface
{
    const JOB_TYPE = null;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function execute(array $job)
    {
        try {
            $this->touchJob($job);

            try {
                $data = $this->getData($job);
            } catch (OptionsResolverException $e) {
                // reject this because the payload data is invalid
                $this->markRejected(
                    $job,
                    'invalid job data',
                    $this->formatExceptionIntoString($e),
                    Job::STATUS_CODE_INVALID_DATA
                );

                return;
            }

            if ($this->process($data, $job)) {
                $this->runSuccessHandler($job);
            }
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }

    /**
     * Setup an options resolver that defines the data that your processor requires (and its defaults if necessary)
     * See: http://symfony.com/doc/current/components/options_resolver.html.
     *
     * Note: if the job data (payload) causes this resolver to throw an exception, the job will be rejected automatically
     * for you
     *
     * @param OptionsResolver $resolver
     */
    abstract public function configureOptions(OptionsResolver $resolver);

    /**
     * this is what needs to be implemented - this method will receive the payload and it needs to be dealt with.
     *
     * @param array $data validated data (the payload)
     * @param array $job  the full job db row array
     */
    abstract public function process(array $data, array $job);

    /**
     * OVERRIDE this method to change how the processor handles uncaught exceptions.
     * You might want to catch various types of exceptions here, or in your process() method.
     *
     * @param array $job
     * @param       $e
     */
    protected function runExceptionHandler(array $job, \Exception $e)
    {
        $this->markExceptionError(
            $job,
            'failed',
            'Failed',
            $e
        );
    }

    /**
     * OVERRIDE this method to change how the processor handles itself after successfully processing.
     *
     * @param array $job
     */
    protected function runSuccessHandler(array $job)
    {
        $this->markComplete($job, 'Successful', '');
    }

    /**
     * Children of AbstractJobProcessor MUST define a JOB_TYPE constant, which matches 1-1 with the passed $job['type']
     * So, if your processor defines JOB_TYPE as "test_job", then any job time we process a job with the "type" field
     * equal to "test_job", it will be processed by this.
     *
     * @param array $job
     *
     * @return string
     */
    public function canHandle(array $job)
    {
        if (!static::JOB_TYPE) {
            throw new JobQueueException(sprintf('The processor "%s" does not define the JOB_TYPE constant', get_class($this)));
        }

        return static::JOB_TYPE == $job['type'];
    }

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /*
     * Mark the job as completed successfully
     *
     * @param  array                        $job
     * @param                               $log_summary
     * @param                               $detailed_logs
     * @param  string                       $status_code
     * @throws \Doctrine\DBAL\DBALException
     */
    public function markComplete(array $job, $log_summary, $detailed_logs, $status_code = Job::STATUS_CODE_SUCCESS)
    {
        $this->connection->executeUpdate(
            '
            UPDATE jobs
            SET log_summary = :log_summary,
                log = :detailed_logs,
                status = :completed_status,
                status_code = :status_code,
                date_touch = :date_touch
            WHERE id = :job_id
            ',
            [
                'log_summary'      => $log_summary,
                'detailed_logs'    => $detailed_logs,
                'completed_status' => Job::STATUS_COMPLETE,
                'status_code'      => $status_code,
                'date_touch'       => new \DateTime(),
                'job_id'           => $job['id'],
            ],
            [
                'log_summary'      => 'string',
                'detailed_logs'    => 'text',
                'completed_status' => 'string',
                'status_code'      => 'string',
                'date_touch'       => 'datetime',
                'job_id'           => 'integer',
            ]
        );
    }

    /*
     * Mark the job as rejected
     *
     * @param  array                        $job
     * @param                               $log_summary
     * @param                               $detailed_logs
     * @param  string                       $status_code
     * @throws \Doctrine\DBAL\DBALException
     */
    public function markRejected(array $job, $log_summary, $detailed_logs, $status_code = Job::STATUS_CODE_INVALID_DATA)
    {
        $this->connection->executeUpdate(
            '
            UPDATE jobs
            SET log_summary = :log_summary,
                log = :detailed_logs,
                status = :completed_status,
                status_code = :status_code,
                date_touch = :date_touch
            WHERE id = :job_id
            ',
            [
                'log_summary'      => $log_summary,
                'detailed_logs'    => $detailed_logs,
                'completed_status' => Job::STATUS_REJECTED,
                'status_code'      => $status_code,
                'date_touch'       => new \DateTime(),
                'job_id'           => $job['id'],
            ],
            [
                'log_summary'      => 'string',
                'detailed_logs'    => 'text',
                'completed_status' => 'string',
                'status_code'      => 'string',
                'date_touch'       => 'datetime',
                'job_id'           => 'integer',
            ]
        );
    }

    /**
     * Mark the job as "error" status, using the exception to provide the detailed log.
     *
     * @param array      $job
     * @param            $status_code
     * @param            $log_summary
     * @param \Exception $e
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function markExceptionError(array $job, $status_code, $log_summary, \Exception $e)
    {
        $this->connection->executeUpdate(
            '
            UPDATE jobs
            SET log_summary = :log_summary,
                log = :detailed_logs,
                status = :error_status,
                status_code = :status_code,
                date_touch = :date_touch,
                has_warning = 1
            WHERE id = :job_id
            ',
            [
                'log_summary'   => $log_summary,
                'detailed_logs' => $this->formatExceptionIntoString($e),
                'error_status'  => Job::STATUS_ERROR,
                'status_code'   => $status_code,
                'date_touch'    => new \DateTime(),
                'job_id'        => $job['id'],
            ],
            [
                'log_summary'   => 'string',
                'detailed_logs' => 'text',
                'error_status'  => 'string',
                'status_code'   => 'string',
                'date_touch'    => 'datetime',
                'job_id'        => 'integer',
            ]
        );
    }

    /**
     * Do the SQL to handle the common retry logic. Make sure to set the job status /logs and such yourself, before this.
     *
     * @param array $job
     * @param       $date_string
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @deprecated this will be deleted soon, inject the JobQueue and use JobQueue->retry(Job) instead
     */
    protected function scheduleRetryExisting(array $job, $date_string)
    {
        $retry_date = new \DateTime($date_string);

        // took out "original_job_id" because this is an EXISTING job we are retrying. That is for external
        // processes to create a new job to retry.
        $this->connection->executeUpdate(
            '
            UPDATE jobs
            SET date_touch = :date_now,
                date_next_try = :date_retry,
                status = :waiting_status,
                worker_id = NULL
            WHERE id = :job_id
            ',
            [
                'date_now'       => new \DateTime(),
                'date_retry'     => $retry_date,
                'waiting_status' => Job::STATUS_WAITING,
                'job_id'         => $job['id'],
            ],
            [
                'date_now'       => 'datetime',
                'date_retry'     => 'datetime',
                'waiting_status' => 'string',
                'job_id'         => 'integer',
            ]
        );
    }

    /**
     * Touch the job - if you are looping or doing a job that takes a long time, touch it every once in a while
     * so the JobSupervisor does not get upset.
     *
     * @param $job
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function touchJob(array $job)
    {
        $this->connection->executeUpdate(
            '
            UPDATE jobs
            SET date_touch = :date_now
            WHERE id = :job_id
            ',
            [
                'date_now' => new \DateTime(),
                'job_id'   => $job['id'],
            ],
            [
                'date_now' => 'datetime',
                'job_id'   => 'integer',
            ]
        );
    }

    protected function getOriginalJobId(array $job)
    {
        return isset($job['original_job_id']) ? $job['original_job_id'] : $job['id'];
    }

    /**
     * Gets the array of data for the job.
     *
     * @param $job
     *
     * @return array
     */
    protected function getData($job)
    {
        $data_array = json_decode($job['data'], true);

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        return $resolver->resolve($data_array);
    }

    /**
     * @param \Exception $e
     *
     * @return string
     */
    protected function formatExceptionIntoString(\Exception $e)
    {
        return sprintf(
            "Exception: %s\nCode: %s\nFile: %s\nLine: %s\n\n%s",
            $e->getMessage(),
            $e->getCode(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }
}
