<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class ActionAlertsService
{
    /**
     * @var Connection
     */
    protected $readConnection;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em, Connection $readConnection)
    {
        $this->em             = $em;
        $this->readConnection = $readConnection;
    }

    /**
     * @param string $visitorId
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int
     */
    public function getLastActionAlert($visitorId)
    {
        $actionAlertRepo = $this->em->getRepository(ActionAlert::class);
        $qb              = $actionAlertRepo->createQueryBuilder('aa');
        $alert           = $qb->where('aa.target_id = :visitorId')
            ->orderBy('aa.id', 'DESC')
            ->setParameter('visitorId', $visitorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $alert ? $alert->getId() : 0;
    }

    /**
     * @param string $visitorId
     * @param int    $lastActionAlert
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    public function getActionAlerts($visitorId, $lastActionAlert)
    {
        $sql = <<<'SQL'
SELECT * FROM `notify_action_alerts`
WHERE (`target_id` = :target_id)
  AND `id` > :last
ORDER BY `id` ASC
SQL;
        $statement = $this->readConnection->prepare($sql);

        $statement->execute([
            'target_id' => $visitorId,
            'last'      => $lastActionAlert,
        ]);

        $all = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($all as &$datum) {
            foreach ($datum as &$innerData) {
                if (is_numeric($innerData)) {
                    $innerData = (int) $innerData;
                }
            }
            $date                  = new \DateTime($datum['date_created']);
            $datum['date_created'] = $date->format(\DateTime::ISO8601);
            if (isset($datum['data'])) {
                $datum['data'] = @json_decode($datum['data'], true);
            }
        }

        return $all;
    }
}
