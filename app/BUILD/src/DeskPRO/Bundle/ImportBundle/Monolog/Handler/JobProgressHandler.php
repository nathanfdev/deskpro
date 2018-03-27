<?php

namespace DeskPRO\Bundle\ImportBundle\Monolog\Handler;

use Application\DeskPRO\Entity\Job;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class JobProgressHandler.
 */
class JobProgressHandler extends AbstractProcessingHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int
     */
    private $jobId;

    /**
     * @var int
     */
    private $lastFlushTime;

    /**
     * @var string
     */
    private $buffer;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param bool|int      $level
     * @param bool          $bubble
     */
    public function __construct(EntityManager $em, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->em            = $em;
        $this->lastFlushTime = time();
    }

    /**
     * @param int $jobId
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * Update job entity with recent log.
     */
    public function flushLog()
    {
        if (!$this->jobId) {
            return;
        }

        $this->em->clear(Job::class);
        $job = $this->em->find(Job::class, $this->jobId);
        if (!$job) {
            return;
        }

        $log = $job->getLog().$this->buffer;
        $log = substr($log, -500 * 1024);
        $job->setLog($log);

        $this->em->persist($job);
        $this->em->flush();

        $this->buffer        = '';
        $this->lastFlushTime = time();
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        if (!$this->jobId) {
            return;
        }

        $this->buffer .= (string) $record['formatted']."\n";
        if ((time() - $this->lastFlushTime) > 3) { // 3sec
            $this->flushLog();
        }
    }
}
