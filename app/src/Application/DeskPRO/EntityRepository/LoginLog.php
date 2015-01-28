<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Settings\Settings;

class LoginLog extends AbstractEntityRepository
{
    /**
     * Gets the last successful login
     *
     * @return \Application\DeskPRO\Entity\LoginLog
     */
    public function getLast(PersonEntity $person)
    {
        // The last login will be the one before the last,
        // since the absolute last will be the current login

        $last_login = $this->_em->createQuery("
            SELECT l
            FROM DeskPRO:LoginLog l
            WHERE l.person = ?0 AND l.is_success = true
            ORDER BY l.id DESC
        ")->setMaxResults(1)->setParameter(0, $person)->getOneOrNullResult();

        return $last_login;
    }

    /**
     * @param PersonEntity $person
     * @param $area
     * @param $maxAttempts
     * @param $time
     * @param $maxLockTime
     * @return int|mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLoginLockoutTime(PersonEntity $person, $maxAttempts, $time, $maxLockTime)
    {
        $time = time() - (int) $time;
        $q = sprintf('
            select count(*) as `count`, max(date_created) as `last` from %1$s where
            person_id = :pid and date_created > :date and id >
            (select ifnull(max(id), 0) from %1$s where person_id = :pid and date_created > :date and is_success = 1)
        ', $this->getTableName());

        $res = $this->getEntityManager()->getConnection()->executeQuery($q, array(
            'pid' => $person['id'],
            'date' => date('Y-m-d H:i:s', $time),
        ))->fetchAll();

        $res = reset($res);
        if (!$res || (int) $maxAttempts > (int) $res['count'] || !$res['last']) {
            return 0;
        }

        $last = strtotime($res['last']);
        $maxLockTime = (int) $maxLockTime;
        $total = $last + $maxLockTime - time();
        return max(0,  $total);
    }
}
