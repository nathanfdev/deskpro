<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\People\PersonGuest;

class RateLimitLog extends AbstractEntityRepository
{
    public function save($action, PersonEntity $person, $ip = null)
    {
        $ip = $ip ? ip2long($ip) : 0;
        $this->getEntityManager()->getConnection()->executeQuery(sprintf(
            'insert into %s (action, ip, person_id, date_created) values (:action, %d, %d, NOW())',
            $this->getTableName(), $ip, $person['id']
        ), array('action' => $action));
    }

    public function count($action, $time, PersonEntity $person, $ip = null)
    {
        $personIsNotGuest = $person && !$person->isGuest();
        if ($personIsNotGuest && $ip) {
            $q = sprintf(
                'select count(*) from %s where action = :action and date_created >= :date and (ip = %d or person_id = %d)',
                $this->getTableName(),
                $ip ? ip2long($ip) : 0,
                $person['id']
            );
        } elseif ($personIsNotGuest) {
            $q = sprintf(
                'select count(*) from %s where action = :action and date_created >= :date and person_id = %d',
                $this->getTableName(),
                $person['id']
            );
        } elseif ($ip) {
            $q = sprintf(
                'select count(*) from %s where action = :action and date_created >= :date and ip = %d',
                $this->getTableName(),
                $ip ? ip2long($ip) : 0
            );
        } else {
            throw new \InvalidArgumentException('either a person with an ID or an IP address are required to count the rate_limit_log');
        }

        $params = array(
            'action' => $action,
            'date'   => date('Y-m-d H:i:s', time() - (int) $time),
        );

        return (int) $this->getEntityManager()->getConnection()->executeQuery($q, $params)->fetchColumn();
    }

    /**
     * @param PersonEntity $person
     * @param string       $action
     * @param int          $time
     * @param int          $maxLockTime
     * @param string|null  $ip
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function getLockoutTime(PersonEntity $person, $action, $time, $maxLockTime, $ip)
    {
        // All we need is just to find latest action attempt, it's written every time,
        // so trying to perform an action while you were locked out will refresh lockout timer
        // therefore user should keep calm and stop hit a button or them will be locked out forever
        $time = (int) $time;

        // if there is no person, then we should use IP for search
        if ($person instanceof PersonGuest) {
            $filter      = 'ip';
            $filterValue = ip2long($ip);
        } else {
            $filter      = 'person_id';
            $filterValue = $person->getId();
        }

        $q = sprintf(
            '
            SELECT `tab`.`date_created` 
            FROM `%s` AS `tab` 
            WHERE `tab`.`%s` = :filter_value 
              AND `tab`.`date_created` > :date 
              AND `tab`.`action` = :action 
            ORDER BY `tab`.`date_created` DESC
            LIMIT 1
            ',
            $this->getTableName(),
            $filter
        );

        $res = $this->getEntityManager()->getConnection()->executeQuery(
            $q,
            [
                'filter_value' => $filterValue,
                'action'       => $action,
                'date'         => date('Y-m-d H:i:s', time() - $time), // last N=$time seconds
            ]
        )->fetchAll();

        // no such records
        if (!$res) {
            return 0;
        }

        $currentAttemptTime = time();
        $lastAttemptTime    = strtotime(current($res)['date_created']);
        $lockTime           = $currentAttemptTime - $lastAttemptTime;

        return max(0, $maxLockTime - $lockTime);
    }
}
