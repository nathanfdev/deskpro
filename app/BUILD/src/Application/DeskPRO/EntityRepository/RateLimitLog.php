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
use Application\DeskPRO\Entity\RateLimitLog as RateLimitLogEntity;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RateLimitLog.
 */
class RateLimitLog extends AbstractEntityRepository
{
    /**
     * @param string       $action
     * @param PersonEntity $person
     * @param string       $ip
     * @param bool         $lockout
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save($action, PersonEntity $person, $ip = null, $lockout = false)
    {
        $log = new RateLimitLogEntity();
        $log
            ->setAction($action)
            ->setIp($ip ?: 0)
            ->setPersonId($person->getId())
            ->setDateCreated(new \DateTime())
            ->setIsLockout($lockout);
        $this->_em->persist($log);
        $this->_em->flush($log);
    }

    /**
     * @param string       $action
     * @param int          $time
     * @param PersonEntity $person
     * @param string       $ip
     *
     * @return int
     */
    public function count($action, $time, PersonEntity $person, $ip = null)
    {
        $date = new \DateTime('-'.(int) $time.' second');
        $qb   = $this->createQueryBuilder('rll');
        $qb
            ->select('COUNT(rll.id)')
            ->andWhere($qb->expr()->eq('rll.action', ':action'))
            ->andWhere($qb->expr()->gte('rll.date_created', ':date'))
            ->andWhere($qb->expr()->gte('rll.is_lockout', 0))
            ->setParameters(
                [
                    'action' => $action,
                    'date'   => $date->format('Y-m-d H:i:s'),
                ]
            );

        $this->applyPersonCondition($qb, $person, $ip);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param PersonEntity $person
     * @param string       $action
     * @param int          $maxLockTime
     * @param string       $ip
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function getLockoutTime(PersonEntity $person, $action, $maxLockTime, $ip)
    {
        // All we need is just to find latest action attempt, it's written every time,
        // so trying to perform an action while you were locked out will refresh lockout timer
        // therefore user should keep calm and stop hit a button or them will be locked out forever
        $currentAttemptTime = time();
        $lastAttemptTime    = $this->getLastAttempt($action, $person, $ip);
        $lockTime           = $currentAttemptTime - $lastAttemptTime;

        return max(0, $maxLockTime - $lockTime);
    }

    /**
     * @param string       $action
     * @param PersonEntity $person
     * @param string       $ip
     *
     * @return bool
     */
    public function getLastLockedOutAttempt($action, PersonEntity $person, $ip = null)
    {
        return $this->getLastAttempt($action, $person, $ip, true);
    }

    /**
     * @param string       $action
     * @param PersonEntity $person
     * @param string       $ip
     * @param bool         $lockout
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return bool
     */
    public function getLastAttempt($action, PersonEntity $person, $ip, $lockout = null)
    {
        $qb = $this->createQueryBuilder('rll');
        $qb
            ->select('rll.date_created')
            ->andWhere($qb->expr()->eq('rll.action', ':action'))
            ->setParameter('action', $action)
            ->addOrderBy('rll.date_created', 'DESC')
            ->setMaxResults(1)
        ;

        if (null !== $lockout) {
            $qb->andWhere($qb->expr()->eq('rll.is_lockout', (bool) $lockout));
        }

        $this->applyPersonCondition($qb, $person, $ip);
        $res = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        return is_array($res) ? strtotime($res['date_created']) : false;
    }

    /**
     * @param QueryBuilder $qb
     * @param PersonEntity $person
     * @param string       $ip
     */
    private function applyPersonCondition(QueryBuilder $qb, PersonEntity $person, $ip)
    {
        $personIsNotGuest = $person && !$person->isGuest();
        if ($personIsNotGuest && $ip) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('rll.ip', ':ip'),
                        $qb->expr()->eq('rll.person_id', ':person')
                    )
                )
                ->setParameter('person', $person)
                ->setParameter('ip', ip2long($ip))
            ;
        } elseif ($personIsNotGuest) {
            $qb->andWhere($qb->expr()->eq('rll.person_id', ':person'))->setParameter('person', $person);
        } elseif ($ip) {
            $qb->andWhere($qb->expr()->eq('rll.ip', ':ip'))->setParameter('ip', ip2long($ip));
        } else {
            throw new \InvalidArgumentException('either a person with an ID or an IP address are required to count the rate_limit_log');
        }
    }
}
