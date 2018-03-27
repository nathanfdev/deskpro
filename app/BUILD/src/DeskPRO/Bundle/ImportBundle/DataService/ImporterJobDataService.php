<?php

namespace DeskPRO\Bundle\ImportBundle\DataService;

use Application\DeskPRO\Entity\Job;
use Doctrine\ORM\EntityManager;

/**
 * Class ImporterJobDataService.
 */
class ImporterJobDataService
{
    const JOB_TYPE = 'importer';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return Job|null
     */
    public function getWaitingJob()
    {
        return $this->em->getRepository(Job::class)->findOneBy([
            'type'   => self::JOB_TYPE,
            'status' => Job::STATUS_WAITING,
        ]);
    }

    /**
     * @return Job|null
     */
    public function getActiveJob()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('j')
            ->from(Job::class, 'j')
            ->where(
                'j.type = :type',
                'j.status IN(:active_statuses)'
            )
            ->setParameter('type', self::JOB_TYPE)
            ->setParameter('active_statuses', [
                Job::STATUS_INSERTING,
                Job::STATUS_WAITING,
                Job::STATUS_RESERVED,
                Job::STATUS_PROCESSING,
            ])
        ;

        $activeJobs = $qb->getQuery()->getResult();

        return $activeJobs ? $activeJobs[0] : null;
    }
}
