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
