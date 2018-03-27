<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Usersource as UsersourceEntity;
use Application\DeskPRO\Entity\UsersourceSyncLog as UsersourceSyncLogEntity;

class UsersourceSyncLog extends AbstractEntityRepository
{
    /**
     * Use this for reporting on if a sync job is in progress or not, and it lets you
     * get information on the last sync.
     *
     * @param UsersourceEntity $usersource
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return UsersourceSyncLogEntity|null
     */
    public function getLastStartedLogForUsersource(UsersourceEntity $usersource)
    {
        // get the most recently started UsersourceSyncLog for this usersource
        return $this->createQueryBuilder('log')
            ->where('log.usersource = :usersource')
            ->setParameter('usersource', $usersource)
            ->orderBy('log.date_start', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * This is the method that the SyncManager uses to fetch or create a log
     * for a running sync usersource to use.
     *
     * @param UsersourceEntity $usersource
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return UsersourceSyncLogEntity|null
     */
    public function getOrCreateLogInProgressForUsersource(UsersourceEntity $usersource)
    {
        // get the most recently started UsersourceSyncLog for this usersource
        $in_progress = $this->createQueryBuilder('log')
            ->where('log.usersource = :usersource')
            ->andWhere('log.date_phase_2_end IS NULL') // no phase 2 end date signals not finished yet
            ->setParameter('usersource', $usersource)
            ->orderBy('log.date_start', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($in_progress && $last_started = $this->getLastStartedLogForUsersource($usersource)) {
            if ($last_started->getDateStart() > $in_progress->getDateStart()) {
                // this is a "stale" log. meaning a log that is in progress but
                // somehow a log has finished since this one started.
                // $last_started and $in_progress would be the same if this was
                // a "legit" answer from the database.
                // the only option is to start a new log, because the one this would
                // normally return is no longer relevant.

                return $this->createNewLog($usersource);
            }
        }

        if (!$in_progress) {
            $in_progress = $this->createNewLog($usersource);
        }

        return $in_progress;
    }

    public function save(UsersourceSyncLogEntity $log)
    {
        $this->_em->persist($log);
        $this->_em->flush($log);
    }

    public function createNewLog(UsersourceEntity $usersource)
    {
        $new = new UsersourceSyncLogEntity();
        $new->setUsersource($usersource);
        $this->_em->persist($new);

        return $new;
    }
}
