<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuseConfig;
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
        $sql = <<<'SQL'
INSERT INTO %s
(`action`, `ip`, `person_id`, `date_created`, `is_lockout`)
VALUES
(:action, :ip, :person_id, :date_created, :is_lockout)
SQL;
        $sql = sprintf($sql, $this->getTableName());
        $this->_em->getConnection()->executeQuery(
            $sql,
            [
                'action'       => $action,
                'ip'           => $ip ?: 0,
                'person_id'    => $person->getId(),
                'date_created' => date('Y-m-d H:i:s'),
                'is_lockout'   => $lockout ? 1 : 0,
            ]
        );
    }

    /**
     * @param AntiAbuseConfig $config
     * @param string          $ip
     *
     * @return int
     */
    public function count(AntiAbuseConfig $config, $ip = null)
    {
        $date = new \DateTime('-'.(int) $config->getTime().' second');
        $qb   = $this->createQueryBuilder('rll');
        $qb
            ->select('COUNT(rll.id)')
            ->andWhere($qb->expr()->eq('rll.action', ':action'))
            ->andWhere($qb->expr()->gte('rll.date_created', ':date'))
            ->andWhere($qb->expr()->gte('rll.is_lockout', 0))
            ->setParameters(
                [
                    'action' => $config->getAction(),
                    'date'   => $date->format('Y-m-d H:i:s'),
                ]
            );

        $this->applyPersonCondition($qb, $config, $ip);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param AntiAbuseConfig $config
     * @param string          $ip
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function getLockoutTime(AntiAbuseConfig $config, $ip)
    {
        // All we need is just to find latest action attempt, it's written every time,
        // so trying to perform an action while you were locked out will refresh lockout timer
        // therefore user should keep calm and stop hit a button or them will be locked out forever
        $currentAttemptTime = time();
        $lastAttemptTime    = $this->getLastAttempt($config, $ip);
        $lockTime           = $currentAttemptTime - $lastAttemptTime;

        return max(0, $config->getLockoutTime() - $lockTime);
    }

    /**
     * @param AntiAbuseConfig $config
     * @param string          $ip
     *
     * @return bool
     */
    public function getLastLockedOutAttempt(AntiAbuseConfig $config, $ip = null)
    {
        return $this->getLastAttempt($config, $ip, true);
    }

    /**
     * @param AntiAbuseConfig $config
     * @param string          $ip
     * @param bool            $lockout
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return bool
     */
    public function getLastAttempt(AntiAbuseConfig $config, $ip, $lockout = null)
    {
        $qb = $this->createQueryBuilder('rll');
        $qb
            ->select('rll.date_created')
            ->andWhere($qb->expr()->eq('rll.action', ':action'))
            ->setParameter('action', $config->getAction())
            ->addOrderBy('rll.date_created', 'DESC')
            ->setMaxResults(1)
        ;

        if (null !== $lockout) {
            $qb->andWhere($qb->expr()->eq('rll.is_lockout', (bool) $lockout));
        }

        $this->applyPersonCondition($qb, $config, $ip);
        $res = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        return is_array($res) ? strtotime($res['date_created']) : false;
    }

    /**
     * @param QueryBuilder    $qb
     * @param AntiAbuseConfig $config
     * @param string          $ip
     */
    private function applyPersonCondition(QueryBuilder $qb, AntiAbuseConfig $config, $ip)
    {
        if (!$config->accountOnlySettings() && $ip) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('rll.ip', ':ip'),
                        $qb->expr()->eq('rll.person_id', ':person')
                    )
                )
                ->setParameter('person', $config->getPerson())
                ->setParameter('ip', $ip)
            ;
        } elseif (!$config->getPerson()->isGuest()) {
            $qb->andWhere($qb->expr()->eq('rll.person_id', ':person'))->setParameter('person', $config->getPerson());
        } elseif ($ip) {
            $qb->andWhere($qb->expr()->eq('rll.ip', ':ip'))->setParameter('ip', $ip);
        } else {
            throw new \InvalidArgumentException('either a person with an ID or an IP address are required to count the rate_limit_log');
        }
    }
}
