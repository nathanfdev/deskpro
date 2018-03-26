<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;

class LoginLog extends AbstractEntityRepository
{
    /**
     * Gets the last successful login.
     *
     * @return \Application\DeskPRO\Entity\LoginLog
     */
    public function getLast(PersonEntity $person)
    {
        // The last login will be the one before the last,
        // since the absolute last will be the current login

        $last_login = $this->_em->createQuery('
            SELECT l
            FROM DeskPRO:LoginLog l
            WHERE l.person = ?0 AND l.is_success = true
            ORDER BY l.id DESC
        ')->setMaxResults(1)->setParameter(0, $person)->getOneOrNullResult();

        return $last_login;
    }

    /**
     * @param PersonEntity $person
     * @param $maxAttempts
     * @param $time
     * @param $maxLockTime
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int|mixed
     */
    public function getLoginLockoutTime(PersonEntity $person, $maxAttempts, $time, $maxLockTime)
    {
        $time = (int) $time;
        $q    = sprintf('
            select date_created from %1$s where
            person_id = :pid and date_created > :date and id >
            (select ifnull(max(id), 0) from %1$s where person_id = :pid and date_created > :date and is_success = 1)
            order by date_created desc
            limit %2$d
        ', $this->getTableName(), $maxAttempts);

        $res = $this->getEntityManager()->getConnection()->executeQuery($q, [
            'pid'  => $person['id'],
            'date' => date('Y-m-d H:i:s', $time),
        ])->fetchAll();

        // no failed attempts
        if (!$res) {
            return 0;
        }

        // find last lockout
        $lastAttemptTime = time();
        $maxRowTime      = null;
        $attempts        = 0;
        foreach ($res as $row) {
            $rowTime = strtotime($row['date_created']);

            if (null === $maxRowTime) {
                $maxRowTime = $rowTime;
            }

            // this entry is from previous lock period
            if ($lastAttemptTime > $rowTime + $maxLockTime) {
                break;
            }

            $lastAttemptTime = $rowTime;
            ++$attempts;
        }

        if ($attempts === (int) $maxAttempts) {
            $lockTime = time() - $maxRowTime;

            return max(0, $maxLockTime - $lockTime);
        }

        return 0;
    }
}
